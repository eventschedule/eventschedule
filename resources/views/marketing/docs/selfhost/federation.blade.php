<x-marketing-layout>
    <x-slot name="title">Federation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Federation</x-slot>
    <x-slot name="description">Share your public events with the eventschedule.com listings. Every listing links back to the event on your own site, so the discovery traffic comes to you.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Federation - Event Schedule",
        "description": "Share your public events with the eventschedule.com listings. Every listing links back to the event on your own site, so the discovery traffic comes to you.",
        "author": {
            "@type": "Organization",
            "name": "Event Schedule"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Event Schedule",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ config('app.url') }}/images/light_logo.png",
                "width": 712,
                "height": 140
            }
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ url()->current() }}"
        },
        "datePublished": "2026-07-26",
        "dateModified": "2026-07-26"
    }
    </script>
    </x-slot>

    @include('marketing.docs.partials.styles')

    <style {!! nonce_attr() !!}>
        .text-gradient-docs {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-docs {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <!-- Hero Section -->
    <section class="es-hero relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5 noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 22% 30%, rgba(37, 99, 235, 0.22), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 80% 70%, rgba(14, 165, 233, 0.2), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="absolute inset-0 grid-pattern"></div>
        </div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Federation" section="selfhost" sectionTitle="Selfhost" sectionRoute="marketing.docs.selfhost" />

            <div class="es-fade-up es-d-2 flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white"><span class="text-gradient-docs">Federation</span></h1>
            </div>
            <p class="es-fade-up es-d-3 text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Share your public events with the eventschedule.com listings, and send the discovery traffic back to your own site.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="bg-white dark:bg-[#0a0a0f] py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                <!-- Sidebar Navigation -->
                <aside class="lg:w-64 flex-shrink-0">
                    <nav class="lg:sticky lg:top-8 space-y-1">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">On this page</div>
                        <a href="#overview" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Overview</a>
                        <a href="#enable" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Turning it on</a>
                        <a href="#per-schedule" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Per-schedule control</a>
                        <a href="#listings" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">What a listing looks like</a>
                        <a href="#sync" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Keeping it in sync</a>
                        <a href="#privacy" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">What is shared</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        @include('marketing.docs.partials.federation-content')
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
