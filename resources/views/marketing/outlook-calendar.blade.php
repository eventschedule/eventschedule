<x-marketing-layout>
    <x-slot name="title">Outlook Calendar Sync & Integration - Event Schedule</x-slot>
    <x-slot name="description">Real-time two-way sync with Outlook and Microsoft 365. OAuth authentication, Microsoft Graph change notifications, and Teams meeting links for smooth event management.</x-slot>
    <x-slot name="breadcrumbTitle">Outlook Calendar</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Outlook Calendar Sync",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Real-time two-way sync with Outlook and Microsoft 365. OAuth authentication, Microsoft Graph change notifications, and Teams meeting links for smooth event management.",
        "featureList": [
            "Two-way Outlook Calendar sync",
            "Microsoft 365 support",
            "OAuth authentication",
            "Microsoft Graph change notifications",
            "Microsoft Teams meeting links"
        ],
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        },
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "Does Outlook Calendar sync work with selfhosted Event Schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes, selfhosted users can set up Outlook Calendar sync by registering their own Microsoft Entra application and configuring the OAuth credentials. Full setup instructions are available in the selfhosted documentation."
                }
            },
            {
                "@type": "Question",
                "name": "Is two-way sync available on the free plan?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes, Outlook Calendar sync is available on all plans, including the free plan. Events you publish appear in Outlook, and events created in Outlook appear in Event Schedule, at no cost."
                }
            },
            {
                "@type": "Question",
                "name": "How quickly do changes sync?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Changes sync in near real time. Microsoft Graph change notifications push updates within seconds, and a 15-minute polling fallback keeps everything in sync if a notification is ever missed."
                }
            },
            {
                "@type": "Question",
                "name": "Does it work with personal Microsoft accounts and work accounts?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Outlook Calendar sync works with both personal Microsoft accounts, such as Outlook.com, Hotmail, and Live, and with Microsoft 365 work or school accounts."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page accent gradient (Outlook blue to blue to sky) */
        .text-gradient-outlook {
            background: linear-gradient(135deg, #0078d4 0%, #2563eb 50%, #0ea5e9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-outlook {
            background: linear-gradient(135deg, #4cc2ff 0%, #60a5fa 50%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        /* On always-dark surfaces, keep the accent bright regardless of page mode */
        .es-finale-panel .text-gradient-outlook {
            background: linear-gradient(135deg, #4cc2ff 0%, #60a5fa 50%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Signature float: a live two-way sync status card */
        .es-outlook-float { animation: es-outlook-bob 5.5s ease-in-out infinite; }
        @keyframes es-outlook-bob {
            0%, 100% { transform: translateY(0) rotate(-0.6deg); }
            50% { transform: translateY(-12px) rotate(0.6deg); }
        }

        /* Signature motif: a row of two-way sync arrows pulsing in sync */
        .es-sync-icon {
            flex: 0 0 auto;
            color: rgba(0, 120, 212, 0.8);
            animation: es-sync-pulse var(--sy-dur, 2.8s) ease-in-out infinite;
            animation-delay: var(--sy-delay, 0s);
        }
        @keyframes es-sync-pulse {
            0%, 100% { opacity: 0.2; transform: scale(0.82); }
            50% { opacity: 0.9; transform: scale(1); filter: drop-shadow(0 0 6px rgba(0, 120, 212, 0.5)); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-outlook-float, .es-sync-icon, .animate-pulse-slow, .animate-sync, .animate-pulse { animation: none !important; }
            .es-sync-icon { opacity: 0.55; transform: none; }
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
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(0, 120, 212, 0.3), rgba(0, 120, 212, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(37, 99, 235, 0.26), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(56, 189, 248, 0.14), rgba(56, 189, 248, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            <!-- Two-way sync motif along the bottom edge -->
            <div class="es-syncs absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-6 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 12; $i++)
                    @php $sz = [18, 26, 16, 22, 30][$i % 5]; $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                    <span class="es-sync-icon" style="--sy-dur: {{ $dur }}s; --sy-delay: {{ $delay }}s;">
                        <svg width="{{ $sz }}" height="{{ $sz }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                    </span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 23 23" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#f25022" d="M1 1h10v10H1z"/>
                    <path fill="#7fba00" d="M12 1h10v10H12z"/>
                    <path fill="#00a4ef" d="M1 12h10v10H1z"/>
                    <path fill="#ffb900" d="M12 12h10v10H12z"/>
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Microsoft 365</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Real-time</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-outlook">Outlook Calendar sync</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Two-way sync with Microsoft 365 and Outlook through the Microsoft Graph API. Connect with OAuth and stay synchronized in near real time.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                    Get started free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="#how-it-works" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    See how it works
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Bento grid                                               -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">

                <!-- OAuth Authentication -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Secure
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">OAuth 2.0 authentication</h2>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Connect with one click using Microsoft's secure OAuth. No passwords stored. Automatic token refresh keeps you connected.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white dark:bg-gray-800">
                                    <svg aria-hidden="true" class="h-6 w-6" viewBox="0 0 23 23" xmlns="http://www.w3.org/2000/svg">
                                        <path fill="#f25022" d="M1 1h10v10H1z"/>
                                        <path fill="#7fba00" d="M12 1h10v10H12z"/>
                                        <path fill="#00a4ef" d="M1 12h10v10H1z"/>
                                        <path fill="#ffb900" d="M12 12h10v10H12z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Sign in with Microsoft</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-400">Secure OAuth connection</div>
                                </div>
                                <svg aria-hidden="true" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Near-real-time Updates -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Real-time
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Near-real-time updates</h2>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Changes in Outlook appear within seconds. Microsoft Graph change notifications push updates, backed by a 15-minute polling fallback.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="flex items-center gap-3">
                                <div class="animate-sync flex h-8 w-8 items-center justify-center rounded-full bg-emerald-500/20">
                                    <svg aria-hidden="true" class="h-4 w-4 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">Event synced</div>
                                    <div class="text-xs text-emerald-500 dark:text-emerald-400">Just now</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Two-way Sync -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            Two-way
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Sync in both directions</h2>
                        <p class="mb-4 text-gray-500 dark:text-gray-400">Events you publish appear in Outlook, and events created in Outlook appear in Event Schedule.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-white/10 dark:bg-white/5">
                                <svg aria-hidden="true" class="h-4 w-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Publish to Outlook</span>
                            </div>
                            <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-white/10 dark:bg-white/5">
                                <svg aria-hidden="true" class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Import from Outlook</span>
                            </div>
                            <div class="flex items-center gap-2 rounded-lg border border-blue-400/30 bg-blue-500/20 p-2">
                                <svg aria-hidden="true" class="h-4 w-4 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <span class="text-sm text-blue-700 dark:text-blue-300">Both directions</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Microsoft Teams Meetings (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-violet-200 bg-violet-100 px-3 py-1.5 text-sm font-medium text-violet-700 dark:border-violet-800/30 dark:bg-violet-900/40 dark:text-violet-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Microsoft Teams
                                </div>
                                <h2 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white">Automatic Teams meeting links</h2>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Turn on Teams links and every online event gets a Microsoft Teams meeting created automatically. The join link is written back to your event.</p>
                            </div>
                            <div class="space-y-3" aria-hidden="true">
                                <div class="flex items-center gap-3 rounded-xl border border-violet-400/30 bg-violet-500/20 p-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/20">
                                        <svg aria-hidden="true" class="h-4 w-4 text-violet-500 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 font-medium text-gray-900 dark:text-white">Online event</div>
                                    <svg aria-hidden="true" class="h-5 w-5 text-violet-500 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10">
                                    <div class="h-3 w-3 rounded-full bg-violet-400"></div>
                                    <div class="flex-1 font-medium text-gray-600 dark:text-gray-300">Teams meeting created</div>
                                </div>
                                <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10">
                                    <div class="h-3 w-3 rounded-full bg-blue-400"></div>
                                    <div class="flex-1 font-medium text-gray-600 dark:text-gray-300">Join link added to event</div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Works with any Microsoft account -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Universal
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Any Microsoft account</h2>
                        <p class="mb-4 text-gray-500 dark:text-gray-400">Connect a personal Microsoft account or a Microsoft 365 work or school account. Both are fully supported.</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-white/10 dark:bg-white/5">
                                <svg aria-hidden="true" class="h-4 w-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Personal account</span>
                            </div>
                            <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 p-2 dark:border-white/10 dark:bg-white/5">
                                <svg aria-hidden="true" class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0H5m14 0h2m-2 0h-4M5 21H3m2 0h4M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Work or school account</span>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. How it works                                             -->
    <!-- ============================================================ -->
    <section id="how-it-works" class="scroll-mt-24 bg-gray-50 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>How it <span class="text-gradient-outlook">works</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Connect once, stay in sync forever.</p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-4" data-reveal-group="90">
                @php
                    $steps = [
                        ['Connect Microsoft', 'Click "Connect Microsoft Calendar" and authorize with OAuth. Your credentials stay secure.'],
                        ['Select calendar', 'Pick which Outlook calendar to sync. Your calendars are discovered automatically.'],
                        ['Enable Teams', 'Optionally turn on Teams links so online events get a join link created for you.'],
                        ['Auto-sync', 'Events sync in near real time via Graph change notifications, with a 15-minute polling fallback.'],
                    ];
                @endphp
                @foreach ($steps as $si => $step)
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-sky-500 text-2xl font-bold text-white shadow-lg shadow-blue-500/25">
                            {{ $si + 1 }}
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{{ $step[0] }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $step[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. What gets synced                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>What gets <span class="text-gradient-outlook">synced</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">All the essential event details, perfectly synchronized.</p>
            </div>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-3" data-reveal-group="70">
                @php
                    $syncs = [
                        ['Event name', 'Subject and title', 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z'],
                        ['Description', 'Full event details', 'M4 6h16M4 10h16M4 14h16M4 18h16'],
                        ['Date & time', 'Start and end times', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['Location', 'Venue address', 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z'],
                        ['Timezone', 'Automatic conversion', 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['Teams link', 'Online meeting join', 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'],
                    ];
                @endphp
                @foreach ($syncs as $sync)
                    <div data-reveal class="rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-50 to-sky-50 p-6 text-center transition-all duration-300 hover:-translate-y-1 dark:border-blue-500/20 dark:from-blue-900/30 dark:to-sky-900/30">
                        <svg aria-hidden="true" class="mx-auto mb-3 h-8 w-8 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            @foreach (explode(' M', $sync[2]) as $pi => $seg)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $pi === 0 ? $seg : 'M'.$seg }}" />
                            @endforeach
                        </svg>
                        <h3 class="mb-1 font-semibold text-gray-900 dark:text-white">{{ $sync[0] }}</h3>
                        <p class="text-sm text-gray-400 dark:text-gray-400">{{ $sync[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Explore More Integrations                                -->
    <!-- ============================================================ -->
    <section class="bg-white py-16 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/integrations') }}" data-reveal class="group block">
                <div class="rounded-3xl border border-gray-200 bg-gradient-to-br from-gray-100 to-gray-200 p-8 transition-all hover:border-gray-300 dark:border-white/10 dark:from-gray-800/50 dark:to-gray-900 dark:hover:border-white/20">
                    <div class="mb-4 inline-flex items-center gap-2 rounded-full bg-gray-200 px-3 py-1 text-sm font-medium text-gray-600 dark:bg-white/15 dark:text-gray-300">
                        <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Calendars
                    </div>
                    <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-gray-700 dark:text-white dark:group-hover:text-gray-200">Explore more integrations</h3>
                    <p class="mb-4 text-gray-500 dark:text-gray-400">Discover all the ways Event Schedule connects with your favorite tools.</p>
                    <span class="inline-flex items-center gap-2 font-medium text-gray-600 transition-all group-hover:gap-3 dark:text-gray-300">
                        View all integrations
                        <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </div>
            </a>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. Related Features                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-8 pb-16 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ marketing_url('/features/calendar-sync') }}" class="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 transition-colors hover:bg-blue-100 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-500/20">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Calendar Sync Features
                </a>
                <a href="{{ marketing_url('/features/ticketing') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                    </svg>
                    Ticketing
                </a>
                <a href="{{ marketing_url('/docs/selfhost/microsoft-calendar') }}" class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-200 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10">
                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Selfhost Setup Guide
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-24 dark:bg-black/30">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-outlook">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Common questions about Outlook Calendar sync.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @php
                    $faqs = [
                        ['Does Outlook Calendar sync work with selfhosted Event Schedule?', 'Yes, selfhosted users can set up Outlook Calendar sync by registering their own Microsoft Entra application and configuring the OAuth credentials. Full setup instructions are available in the selfhosted documentation.'],
                        ['Is two-way sync available on the free plan?', 'Yes, Outlook Calendar sync is available on all plans, including the free plan. Events you publish appear in Outlook, and events created in Outlook appear in Event Schedule, at no cost.'],
                        ['How quickly do changes sync?', 'Changes sync in near real time. Microsoft Graph change notifications push updates within seconds, and a 15-minute polling fallback keeps everything in sync if a notification is ever missed.'],
                        ['Does it work with personal Microsoft accounts and work accounts?', 'Yes. Outlook Calendar sync works with both personal Microsoft accounts, such as Outlook.com, Hotmail, and Live, and with Microsoft 365 work or school accounts.'],
                    ];
                @endphp
                @foreach ($faqs as [$q, $a])
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $q }}</h3>
                            <svg aria-hidden="true" class="ml-4 h-5 w-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
    <!-- 8. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(0, 120, 212, 0.3), rgba(0, 120, 212, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-syncs absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-6 px-8 opacity-45" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 9; $i++)
                            @php $sz = [18, 26, 16, 22, 30][$i % 5]; $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                            <span class="es-sync-icon" style="--sy-dur: {{ $dur }}s; --sy-delay: {{ $delay }}s;">
                                <svg width="{{ $sz }}" height="{{ $sz }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                            </span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Connect your <span class="text-gradient-outlook">Outlook Calendar</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Set up in seconds. Your events stay synchronized automatically.
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
