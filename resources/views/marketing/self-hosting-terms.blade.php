<x-marketing-layout>
    <x-slot name="title">Self-Hosting Terms - Event Schedule</x-slot>
    <x-slot name="description">Terms for self-hosting Event Schedule - the rules and guidelines for running your own instance.</x-slot>
    <x-slot name="breadcrumbTitle">Selfhosting Terms</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Self-Hosting Terms - Event Schedule",
        "description": "Terms for self-hosting Event Schedule - the rules and guidelines for running your own instance.",
        "url": "{{ url()->current() }}",
        "isPartOf": {
            "@type": "WebSite",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "about": {
            "@type": "Thing",
            "name": "Self-Hosting Terms"
        }
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        .text-gradient-legal {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-legal {
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

    <!-- Header -->
    <section class="es-hero relative overflow-hidden bg-white py-20 dark:bg-[#0a0a0f] noise sm:py-24">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 20% 60%, rgba(37, 99, 235, 0.24), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 80% 30%, rgba(14, 165, 233, 0.2), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="absolute inset-0 grid-pattern"></div>
        </div>
        <div class="relative z-10 mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-2">
                <svg aria-hidden="true" class="h-4 w-4 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Selfhost</span>
            </div>
            <h1 class="es-balance es-fade-up es-d-2 text-4xl font-black tracking-tight text-gray-900 dark:text-white sm:text-5xl">Self-Hosting <span class="text-gradient-legal">Terms</span></h1>
            <p class="es-fade-up es-d-3 mt-4 text-lg text-gray-600 dark:text-gray-400">Event Schedule LLC</p>
        </div>
    </section>

    <!-- Content -->
    <section class="py-16 bg-white dark:bg-[#0a0a0f]">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="prose prose-lg dark:prose-invert max-w-none">
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    All features from the hosted app are included in the open-source code. By self-hosting Event Schedule, you accept the platform "as is" and "with all faults," assuming all risks associated with running and maintaining your own instance. Use of the source code is governed by the <strong>Attribution Assurance License</strong>; these Terms govern your relationship with Event Schedule as a service and support provider.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Data Ownership & Access</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Self-hosters own their data and bear full responsibility for it. <strong>Event Schedule cannot access, modify, or remove self-hosted data</strong> stored on your private infrastructure. Users must handle any losses or damages affecting their clients independently.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Amendment Rights</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Event Schedule may modify these terms regarding support, connected services, and future releases with notice via email, dashboards, or websites. Changes become binding seven days after notice, unless longer periods apply by law. While the open-source license for a specific version of the code is permanent, users must accept updated Terms to continue receiving official updates or technical support.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Eligibility</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Users must be 18 years of age or older and confirm compliance with applicable laws. Prior suspension or removal from Event Schedule services disqualifies new access to our official support and update channels.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Personal Responsibility</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Users are responsible for securing their credentials and handling all legal obligations regarding data privacy, copyright, and international regulations independently.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Your Obligations</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Users remain solely responsible for goods, services, and customer obligations facilitated through the platform.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Customer Service</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Users provide their own customer support. Event Schedule only assists account users with platform functionality on the hosted version or via specific enterprise support agreements.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Data Use & Privacy</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    For self-hosted instances, <strong>this section applies only to data explicitly transmitted to Event Schedule</strong> (e.g., via opted-in crash reports, update checks, or cloud-relay features). In such cases, you grant Event Schedule a non-exclusive, fully sublicensable, worldwide, royalty-free right to use, copy, and store that specific data solely for the purpose of providing services to your instance.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Restricted Businesses & Sanctions</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Illegal activities and use in high-risk jurisdictions (Cuba, Iran, North Korea, Crimea, Syria) are prohibited. While the software is open source, Event Schedule does not provide support or services to prohibited categories, including gambling, telemarketing, unauthorized multi-level marketing, or weapons sales.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Indemnity & Liability</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    Users indemnify Event Schedule against claims arising from platform use, agreement violations, or third-party rights infringement. Event Schedule disclaims liability for damages, lost profits, or data loss, as further detailed in the Attribution Assurance License.
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">Communication & Resolution</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    For privacy and data concerns, contact: <a href="mailto:legal@eventschedule.com" class="text-blue-600 dark:text-blue-400 hover:underline">legal@eventschedule.com</a>
                </p>
            </div>
        </div>
    </section>
</x-marketing-layout>
