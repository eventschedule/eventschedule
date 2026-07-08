<x-marketing-layout>
    <x-slot name="title">Custom Labels for Schedules - Event Schedule</x-slot>
    <x-slot name="description">Override the default labels in your schedule. Rename "Events" to "Shows" or "Venue" to "Location", with multi-language auto-translation.</x-slot>
    <x-slot name="breadcrumbTitle">Custom Labels</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "What are custom labels?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Custom labels let you rename the default terms used in your schedule. For example, you can change 'Events' to 'Shows', 'Venue' to 'Location', or 'Talent' to 'Artist' to match your community's language."
                }
            },
            {
                "@type": "Question",
                "name": "Which labels can I customize?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "You can customize labels for events, venues, talent, curators, sub-schedules, and other key terms that appear throughout your schedule. Select from a predefined list of alternatives for each label."
                }
            },
            {
                "@type": "Question",
                "name": "Do custom labels work with translations?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. When you select a custom label, it is automatically translated into all supported languages. Your schedule displays the correct label regardless of the viewer's language setting."
                }
            }
        ]
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Custom Labels",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Event Management Software",
        "operatingSystem": "Web",
        "description": "Override default labels in your event schedule. Rename 'Events' to 'Shows', 'Venue' to 'Location', and more. Multi-language support with auto-translation. Pro feature. No credit card required.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Free trial, Pro plan feature"
        },
        "featureList": [
            "Override default schedule labels",
            "Multi-language support",
            "Per-schedule configuration",
            "Predefined label alternatives",
            "Automatic translation to 11 languages",
            "Pro and Enterprise plans"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    </x-slot>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* For custom-labels "The Rename" styles. The shared es-* motion system lives in
           marketing.css; this holds the teal glow gradient, the drifting rename card,
           and the label-tag motif. */
        .text-gradient-labels {
            background: linear-gradient(135deg, #0d9488, #14b8a6, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(13, 148, 136, 0.3);
        }
        .dark .text-gradient-labels {
            background: linear-gradient(135deg, #2dd4bf, #5eead4, #22d3ee);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(45, 212, 191, 0.3);
        }
        @keyframes es-lbl-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-lbl-float { animation: es-lbl-float 6s ease-in-out infinite; }

        /* Label-tag motif: pills of varying widths pulse in a wave, like a tag cloud. */
        .es-tags { display: flex; align-items: center; }
        .es-tag {
            flex: 0 0 auto; height: 10px; border-radius: 9999px;
            background: linear-gradient(to right, rgba(13, 148, 136, 0.55), rgba(6, 182, 212, 0.55));
            animation: es-tag-pulse var(--tg-dur, 2.6s) ease-in-out infinite;
            animation-delay: var(--tg-delay, 0s);
        }
        @keyframes es-tag-pulse {
            0%, 100% { opacity: 0.25; transform: scaleX(0.85); }
            50% { opacity: 0.95; transform: scaleX(1); box-shadow: 0 0 8px rgba(13, 148, 136, 0.5); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-lbl-float, .es-tag { animation: none !important; }
            .es-tag { opacity: 0.55; }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: custom labels                                       -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(13, 148, 136, 0.3), rgba(13, 148, 136, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(6, 182, 212, 0.28), rgba(6, 182, 212, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(20, 184, 166, 0.14), rgba(20, 184, 166, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Label tags along the bottom edge -->
            <div class="es-tags absolute bottom-0 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-3 px-8 opacity-45 md:flex" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                @for ($i = 0; $i < 22; $i++)
                    @php $w = [30, 48, 38, 56, 42][$i % 5]; $dur = 2.2 + ($i % 5) * 0.28; $delay = ($i % 11) * 0.18; @endphp
                    <span class="es-tag" style="width: {{ $w }}px; --tg-dur: {{ $dur }}s; --tg-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Customization</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Custom labels</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-labels">for your schedule</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Override default terms to match your community's language. Rename "Events" to "Shows", "Venue" to "Location", and more.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-teal-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-teal-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-teal-500/40">
                    Get started free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.creating_schedules') }}#customize-custom-labels" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Custom Labels guide
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Bento features                                           -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-12 max-w-3xl text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Make your schedule speak your <span class="text-gradient-labels">language</span></h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="90">

                <!-- Override labels -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                            Rename
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Override labels</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Replace default terms like "Events", "Venue", and "Talent" with words that fit your community.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" aria-hidden="true">
                            <div class="es-ai-field mb-2 flex items-center justify-between" style="--i: 0;"><span class="text-[10px] text-gray-400 line-through dark:text-gray-500">Events</span><svg class="h-3 w-3 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg><span class="text-[10px] font-medium text-gray-900 dark:text-white">Shows</span></div>
                            <div class="es-ai-field flex items-center justify-between" style="--i: 1;"><span class="text-[10px] text-gray-400 line-through dark:text-gray-500">Venue</span><svg class="h-3 w-3 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg><span class="text-[10px] font-medium text-gray-900 dark:text-white">Location</span></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Multi-language support -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m10.5 21 5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802" /></svg>
                            Languages
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Multi-language support</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Custom labels work across all supported languages. Your renamed terms display correctly for every visitor.</p>
                        <div class="mt-auto space-y-1 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" aria-hidden="true">
                            @foreach ([['EN', 'Shows'], ['ES', 'Espect&aacute;culos'], ['FR', 'Spectacles']] as $li => [$lang, $word])
                                <div class="es-ai-field flex items-center justify-between" style="--i: {{ $li }};"><span class="text-[10px] text-gray-600 dark:text-gray-300">{{ $lang }}</span><span class="text-[10px] font-medium text-gray-900 dark:text-white">{!! $word !!}</span></div>
                            @endforeach
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Per-schedule settings -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75M10.5 18a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 18H7.5m3-6h9.75M10.5 12a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 12H7.5" /></svg>
                            Settings
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Per-schedule settings</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Each schedule has its own label configuration. A music venue and a conference can use different terms.</p>
                        <div class="mt-auto space-y-1.5 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2" style="--i: 0;"><span class="rounded-full bg-teal-100 px-2 py-0.5 text-[10px] text-teal-700 dark:bg-teal-500/20 dark:text-teal-300">Jazz Club</span><span class="text-[10px] text-gray-500 dark:text-gray-400">Shows, Artists</span></div>
                            <div class="es-ai-field flex items-center gap-2" style="--i: 1;"><span class="rounded-full bg-cyan-100 px-2 py-0.5 text-[10px] text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300">Tech Conf</span><span class="text-[10px] text-gray-500 dark:text-gray-400">Sessions, Speakers</span></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Select from list -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                            Selection
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Select from list</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Choose from a curated list of alternative labels. No free-text input needed, so translations stay consistent.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" aria-hidden="true">
                            <div class="mb-2 text-[10px] text-gray-500 dark:text-gray-400">Replace "Events" with:</div>
                            <div class="flex flex-wrap gap-1.5">
                                <span class="rounded-full bg-teal-200 px-2 py-0.5 text-[10px] text-teal-800 ring-1 ring-teal-400/50 dark:bg-teal-500/30 dark:text-teal-200">Shows</span>
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-600 dark:bg-white/5 dark:text-gray-400">Sessions</span>
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-600 dark:bg-white/5 dark:text-gray-400">Classes</span>
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-600 dark:bg-white/5 dark:text-gray-400">Performances</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Auto-translation -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 0 0-2.455 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" /></svg>
                            Auto
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Auto-translation</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Selected labels are automatically translated into all supported languages. No manual translation work required.</p>
                        <div class="mt-auto space-y-2 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-2" style="--i: 0;"><svg class="h-3.5 w-3.5 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg><span class="text-[10px] text-gray-600 dark:text-gray-300">11 languages supported</span></div>
                            <div class="es-ai-field flex items-center gap-2" style="--i: 1;"><svg class="h-3.5 w-3.5 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg><span class="text-[10px] text-gray-600 dark:text-gray-300">Zero configuration needed</span></div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Pro feature -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                            Pro
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Pro feature</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Custom labels are available on Pro and Enterprise plans. Tailor your schedule's terminology to your audience.</p>
                        <div class="mt-auto flex flex-wrap gap-3">
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Pro plan</span>
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Enterprise plan</span>
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Per-schedule config</span>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. One label, every language (dark band)                    -->
    <!-- ============================================================ -->
    <section class="bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(13, 148, 136, 0.24), rgba(13, 148, 136, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(6, 182, 212, 0.2), rgba(6, 182, 212, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-tags absolute bottom-0 left-0 right-0 mx-auto flex h-14 max-w-4xl items-center justify-center gap-3 px-8 opacity-30" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                    @for ($i = 0; $i < 20; $i++)
                        @php $w = [30, 48, 38, 56, 42][$i % 5]; $dur = 2.2 + ($i % 5) * 0.28; $delay = ($i % 11) * 0.18; @endphp
                        <span class="es-tag" style="width: {{ $w }}px; --tg-dur: {{ $dur }}s; --tg-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>One label. <span class="text-gradient-labels">Every language.</span></h2>
                <p class="mb-12 text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Pick a term once, and it auto-translates everywhere your schedule is read.</p>

                <div data-reveal class="mx-auto mb-8 inline-flex items-center gap-4 rounded-2xl border border-white/10 bg-white/[0.04] px-6 py-4">
                    <span class="font-mono text-lg text-gray-500 line-through sm:text-xl">Events</span>
                    <svg aria-hidden="true" class="h-6 w-6 text-teal-400 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                    <span class="text-2xl font-black text-white sm:text-3xl"><span class="text-gradient-labels">Shows</span></span>
                </div>

                <div class="flex flex-wrap justify-center gap-2" data-reveal-group="60">
                    @foreach (['EN Shows', 'ES Espect&aacute;culos', 'FR Spectacles', 'DE Auff&uuml;hrungen', 'IT Spettacoli', 'PT Espet&aacute;culos', '+5 more'] as $chip)
                        <span data-reveal class="rounded-full border border-white/10 bg-white/[0.06] px-3 py-1.5 text-sm text-gray-300"><span class="font-mono text-teal-300">{!! substr($chip, 0, 2) !!}</span> {!! substr($chip, 3) !!}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-labels">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about custom labels.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['What are custom labels?', 'Custom labels let you rename the default terms used throughout your schedule. For example, you can change "Events" to "Shows", "Venue" to "Location", or "Talent" to "Artist" to match your community\'s language.'],
                    ['Which labels can I customize?', 'You can customize labels for events, venues, talent, curators, sub-schedules, and other key terms that appear throughout your schedule. Select from a predefined list of alternatives for each label.'],
                    ['Do custom labels work with translations?', 'Yes. When you select a custom label, it is automatically translated into all supported languages. Your schedule displays the correct label regardless of the viewer\'s language setting.'],
                ] as [$q, $a])
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $q }}</h3>
                            <svg aria-hidden="true" class="ml-4 w-5 h-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-teal-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(13, 148, 136, 0.3), rgba(13, 148, 136, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-tags absolute bottom-0 left-0 right-0 mx-auto flex h-14 max-w-3xl items-center justify-center gap-3 px-8 opacity-30" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                        @for ($i = 0; $i < 18; $i++)
                            @php $w = [30, 48, 38, 56, 42][$i % 5]; $dur = 2.2 + ($i % 5) * 0.28; $delay = ($i % 11) * 0.18; @endphp
                            <span class="es-tag" style="width: {{ $w }}px; --tg-dur: {{ $dur }}s; --tg-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Your schedule, <span class="text-gradient-labels">your words</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Start customizing your schedule's labels today. No credit card required.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-teal-600 to-cyan-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-teal-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-teal-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Start for free
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-gray-400">No credit card required</p>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
