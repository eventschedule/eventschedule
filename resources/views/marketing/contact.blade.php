<x-marketing-layout>
    <x-slot name="title">Contact Event Schedule | Get in Touch</x-slot>
    <x-slot name="description">Get in touch with Event Schedule. Contact us via email for support, report issues on GitHub, or connect on social media. We're here to help with setup, features, and more.</x-slot>
    <x-slot name="breadcrumbTitle">Contact</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "ContactPage",
        "name": "Contact Event Schedule",
        "description": "Get in touch with Event Schedule. Reach out via email, social media, or report issues on GitHub.",
        "url": "{{ url()->current() }}",
        "mainEntity": {
            "@type": "Organization",
            "name": "Event Schedule",
            "email": "{{ config('app.support_email') }}",
            "url": "{{ config('app.url') }}"
        }
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        .text-gradient-contact {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-contact {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .es-finale-panel .text-gradient-contact {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Signature float: a live message-compose card */
        .es-contact-float { animation: es-contact-bob 5.5s ease-in-out infinite; }
        @keyframes es-contact-bob {
            0%, 100% { transform: translateY(0) rotate(-0.6deg); }
            50% { transform: translateY(-12px) rotate(0.6deg); }
        }

        /* Signature motif: a row of envelopes pulsing (get in touch) */
        .es-envelope {
            flex: 0 0 auto;
            color: rgba(37, 99, 235, 0.8);
            animation: es-envelope-pulse var(--ev-dur, 2.8s) ease-in-out infinite;
            animation-delay: var(--ev-delay, 0s);
        }
        @keyframes es-envelope-pulse {
            0%, 100% { opacity: 0.2; transform: scale(0.82); }
            50% { opacity: 0.9; transform: scale(1); filter: drop-shadow(0 0 6px rgba(37, 99, 235, 0.5)); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-contact-float, .es-envelope, .animate-pulse-slow { animation: none !important; }
            .es-envelope { opacity: 0.55; transform: none; }
        }
    </style>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <!-- ============================================================ -->
    <!-- 1. Hero                                                     -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(64svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            <!-- Envelope motif along the bottom edge -->
            <div class="es-envelopes absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-6 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 12; $i++)
                    @php $sz = [18, 26, 16, 22, 30][$i % 5]; $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                    <span class="es-envelope" style="--ev-dur: {{ $dur }}s; --ev-delay: {{ $delay }}s;">
                        <svg width="{{ $sz }}" height="{{ $sz }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    </span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Get in touch</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Contact</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-contact">Us</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                We'd love to hear from you. Reach out through any of the channels below.
            </p>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 2. Contact Methods                                          -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            <!-- Email - Prominent Top Card -->
            <div data-reveal="panel" class="relative mx-auto mb-10 max-w-[52rem] overflow-hidden rounded-3xl border border-gray-200 bg-gradient-to-br from-blue-100 to-sky-100 p-8 transition-all duration-300 hover:scale-[1.01] dark:border-white/10 dark:from-blue-900 dark:to-sky-900 md:p-12">
                <div class="pointer-events-none absolute right-0 top-0 h-72 w-72 rounded-full bg-blue-500/10 blur-[100px]" aria-hidden="true"></div>
                <div class="pointer-events-none absolute bottom-0 left-0 h-64 w-64 rounded-full bg-sky-500/10 blur-[100px]" aria-hidden="true"></div>
                <div class="relative flex items-center justify-between">
                    <div>
                        <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-blue-200 px-3 py-1 text-sm font-medium text-blue-700 dark:bg-white/10 dark:text-blue-200">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Email
                        </div>
                        <h2 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white md:text-4xl">Get in Touch</h2>
                        <p class="mb-8 text-lg text-gray-500 dark:text-blue-100/70">
                            For general inquiries, feature requests, or support.
                        </p>
                        <a href="mailto:{{ config('app.support_email') }}" class="inline-flex items-center gap-3 rounded-2xl bg-white px-6 py-3 font-semibold text-blue-900 transition-all hover:bg-blue-50">
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ config('app.support_email') }}
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                        </a>
                    </div>
                    <div class="ml-8 hidden shrink-0 md:block" aria-hidden="true">
                        <svg class="h-32 w-32 text-white/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Open Source Community Header -->
            <div class="mb-10 text-center" data-reveal>
                <svg aria-hidden="true" class="mx-auto mb-4 h-16 w-16 text-gray-900 dark:text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                </svg>
                <h3 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">Open Source Community</h3>
                <p class="text-gray-600 dark:text-gray-400">Event Schedule is open source. Join us on GitHub.</p>

                @include('marketing.partials.github-star-badge')
            </div>

            <!-- GitHub Cards Grid -->
            <div class="mb-10 grid grid-cols-1 gap-8 md:grid-cols-2" data-reveal-group="80">
                <!-- Discussions Card -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-6 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-200">
                            <svg aria-hidden="true" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                            </svg>
                            Discussions
                        </div>
                        <h4 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Community Discussions</h4>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">
                            Have a question or idea? Start a conversation on GitHub Discussions. It's a great way to connect with us and the community.
                        </p>
                        <a href="https://github.com/eventschedule/eventschedule/discussions" target="_blank" rel="noopener noreferrer" class="mt-auto inline-flex items-center gap-2 self-start rounded-xl border border-blue-300 bg-blue-100 px-5 py-2.5 font-medium text-blue-900 transition-all hover:bg-blue-200 dark:border-white/20 dark:bg-white/10 dark:text-white dark:hover:bg-white/20">
                            GitHub Discussions
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </a>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Issues Card -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-6 inline-flex items-center gap-2 self-start rounded-full border border-gray-200 bg-gray-100 px-3 py-1 text-sm font-medium text-gray-600 dark:border-white/10 dark:bg-white/10 dark:text-gray-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                            </svg>
                            Issues
                        </div>
                        <h4 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Report a Bug</h4>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">
                            Found a bug or have a technical issue? Open an issue on GitHub and we'll look into it.
                        </p>
                        <a href="https://github.com/eventschedule/eventschedule/issues" target="_blank" rel="noopener noreferrer" class="mt-auto inline-flex items-center gap-2 self-start rounded-xl border border-gray-300 bg-gray-200 px-5 py-2.5 font-medium text-gray-900 transition-all hover:bg-gray-300 dark:border-white/20 dark:bg-white/10 dark:text-white dark:hover:bg-white/20">
                            GitHub Issues
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                        </a>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>
            </div>

            <!-- Social Media -->
            <div data-reveal class="rounded-3xl border border-gray-200 bg-gradient-to-br from-gray-50 to-slate-50 p-8 shadow-sm dark:border-white/10 dark:from-white/5 dark:to-white/[0.02]">
                <h3 class="mb-3 text-center text-2xl font-bold text-gray-900 dark:text-white">Follow Us</h3>
                <p class="mb-8 text-center text-gray-600 dark:text-gray-400">
                    Stay up to date with the latest news, features, and updates.
                </p>
                <div class="flex flex-wrap justify-center gap-6">
                    <a href="https://www.facebook.com/appeventschedule" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 rounded-xl bg-gray-100 px-6 py-3 transition-colors hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20">
                        <svg aria-hidden="true" class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Facebook</span>
                    </a>
                    <a href="https://www.instagram.com/eventschedule/" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 rounded-xl bg-gray-100 px-6 py-3 transition-colors hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20">
                        <svg aria-hidden="true" class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Instagram</span>
                    </a>
                    <a href="https://youtube.com/@EventSchedule" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 rounded-xl bg-gray-100 px-6 py-3 transition-colors hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20">
                        <svg aria-hidden="true" class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.418 4.814a2.504 2.504 0 0 1-1.768 1.768c-1.56.419-7.814.419-7.814.419s-6.255 0-7.814-.419a2.505 2.505 0 0 1-1.768-1.768C2 15.255 2 12 2 12s0-3.255.417-4.814a2.507 2.507 0 0 1 1.768-1.768C5.744 5 11.998 5 11.998 5s6.255 0 7.814.418ZM15.194 12 10 15V9l5.194 3Z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">YouTube</span>
                    </a>
                    <a href="https://x.com/ScheduleEvent" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 rounded-xl bg-gray-100 px-6 py-3 transition-colors hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20">
                        <svg aria-hidden="true" class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">X (Twitter)</span>
                    </a>
                    <a href="https://www.linkedin.com/company/eventschedule/" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 rounded-xl bg-gray-100 px-6 py-3 transition-colors hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20">
                        <svg aria-hidden="true" class="h-5 w-5 text-gray-700 dark:text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">LinkedIn</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Helpful Resources                                        -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div data-reveal="panel" class="relative overflow-hidden rounded-3xl border border-blue-200 bg-gradient-to-br from-blue-100 to-sky-100 p-8 dark:border-white/10 dark:from-blue-900/30 dark:to-sky-900/30 lg:p-12">
                <div class="pointer-events-none absolute right-0 top-0 h-64 w-64 rounded-full bg-blue-500/10 blur-[80px]" aria-hidden="true"></div>
                <div class="relative">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-blue-500/20 px-3 py-1 text-sm font-medium text-blue-700 dark:text-blue-300">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Helpful resources
                    </div>
                    <h2 class="mb-6 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-4xl">You can also find answers here</h2>
                    <p class="mb-8 text-lg leading-relaxed text-gray-600 dark:text-gray-300">
                        You can also check our documentation and FAQ where many common questions are already answered.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ marketing_url('/docs') }}" class="inline-flex items-center gap-2 rounded-2xl border border-gray-300 bg-gray-200 px-6 py-3 font-medium text-gray-900 transition-all hover:bg-gray-300 dark:border-white/20 dark:bg-white/10 dark:text-white dark:hover:bg-white/20">
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Documentation
                        </a>
                        <a href="{{ marketing_url('/faq') }}" class="inline-flex items-center gap-2 rounded-2xl border border-gray-300 bg-gray-200 px-6 py-3 font-medium text-gray-900 transition-all hover:bg-gray-300 dark:border-white/20 dark:bg-white/10 dark:text-white dark:hover:bg-white/20">
                            <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            FAQ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Ready to <span class="text-gradient-contact">get started?</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Create your free schedule today. No credit card required.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-blue-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}"></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
