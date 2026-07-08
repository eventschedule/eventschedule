<x-marketing-layout>
    <x-slot name="title">{{ __('messages.why_create_account_title') }} | Event Schedule</x-slot>
    <x-slot name="description">{{ __('messages.why_create_account_description') }}</x-slot>
    <x-slot name="breadcrumbTitle">{{ __('messages.why_create_account_title') }}</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "WebPage",
        "name": "Why Create an Account - Event Schedule",
        "description": "Discover the benefits of creating a free Event Schedule account.",
        "url": "{{ url()->current() }}",
        "isPartOf": {
            "@type": "WebSite",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        }
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page accent gradient (blue to sky to cyan) */
        .text-gradient-wca {
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 50%, #06b6d4 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .dark .text-gradient-wca {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .es-finale-panel .text-gradient-wca {
            background: linear-gradient(135deg, #60a5fa 0%, #38bdf8 50%, #22d3ee 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Signature float: an account-benefits card */
        .es-account-float { animation: es-account-bob 5.5s ease-in-out infinite; }
        @keyframes es-account-bob {
            0%, 100% { transform: translateY(0) rotate(-0.6deg); }
            50% { transform: translateY(-12px) rotate(0.6deg); }
        }

        /* Signature motif: a row of keys (unlock your account benefits) */
        .es-key {
            flex: 0 0 auto;
            color: rgba(37, 99, 235, 0.8);
            animation: es-key-pulse var(--k-dur, 2.8s) ease-in-out infinite;
            animation-delay: var(--k-delay, 0s);
        }
        @keyframes es-key-pulse {
            0%, 100% { opacity: 0.2; transform: scale(0.82) rotate(-8deg); }
            50% { opacity: 0.9; transform: scale(1) rotate(0deg); filter: drop-shadow(0 0 6px rgba(37, 99, 235, 0.5)); }
        }

        @media (prefers-reduced-motion: reduce) {
            .es-account-float, .es-key, .animate-pulse-slow, .animate-float { animation: none !important; }
            .es-key { opacity: 0.55; transform: none; }
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
    <section class="es-hero relative flex min-h-[calc(78svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(14, 165, 233, 0.26), rgba(14, 165, 233, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="absolute inset-0 grid-pattern"></div>

            <!-- Key motif along the bottom edge (unlock account benefits) -->
            <div class="es-keys absolute bottom-8 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-6 px-8 opacity-55 md:flex" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                @for ($i = 0; $i < 12; $i++)
                    @php $sz = [18, 26, 16, 22, 30][$i % 5]; $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                    <span class="es-key" style="--k-dur: {{ $dur }}s; --k-delay: {{ $delay }}s;">
                        <svg width="{{ $sz }}" height="{{ $sz }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4-2a6 6 0 01-7.743 5.743L11 14H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                    </span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">{{ __('messages.why_create_account_hero_badge') }}</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">{{ __('messages.why_create_account_hero_headline') }}</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-wca">{{ __('messages.free') }}</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                {{ __('messages.why_create_account_hero_subtitle') }}
            </p>

            <div class="es-fade-up es-d-3 flex flex-wrap justify-center gap-4">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-sky-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-blue-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-500/40">
                    {{ __('messages.why_create_account_cta') }}
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1" @if(in_array(request()->get('lang'), ['he', 'ar'])) style="transform: rotate(180deg)" @endif fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Guest vs Account Comparison                              -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-8 md:grid-cols-2" data-reveal-group="90">
                <!-- Without Account -->
                <div data-reveal class="rounded-2xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/5">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 dark:bg-white/10">
                            <svg aria-hidden="true" class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('messages.why_create_account_without') }}</h2>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 text-gray-600 dark:text-gray-400">
                            <svg aria-hidden="true" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>{{ __('messages.why_create_account_submit_only') }}</span>
                        </div>
                    </div>
                </div>

                <!-- With Account -->
                <div data-reveal class="rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-100 to-sky-100 p-8 dark:border-blue-500/30 dark:from-blue-900 dark:to-sky-900">
                    <div class="mb-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-500">
                            <svg aria-hidden="true" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">{{ __('messages.why_create_account_with') }}</h2>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 text-gray-700 dark:text-gray-300">
                            <svg aria-hidden="true" class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>{{ __('messages.why_create_account_everything') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Bento Grid Features                                      -->
    <!-- ============================================================ -->
    <section class="bg-white py-24 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">

                <!-- Customize Your Profile (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col items-center gap-8 lg:flex-row">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ __('messages.profile') }}
                                </div>
                                <h2 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white lg:text-4xl">{{ __('messages.why_create_account_customize_title') }}</h2>
                                <p class="text-lg text-gray-500 dark:text-gray-400">{{ __('messages.why_create_account_customize_desc') }}</p>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="max-w-xs rounded-2xl border border-blue-300 bg-gradient-to-br from-blue-100 to-sky-100 p-4 dark:border-blue-400/30 dark:from-blue-950 dark:to-sky-950">
                                    <div class="mb-3 flex items-center gap-3">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-sky-500">
                                            <svg aria-hidden="true" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">Your Name</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Event Organizer</div>
                                        </div>
                                    </div>
                                    <div class="mb-3 h-16 rounded-lg bg-gradient-to-r from-blue-400/30 to-sky-400/30"></div>
                                    <div class="flex gap-2">
                                        <div class="h-2 flex-1 rounded bg-blue-300 dark:bg-white/20"></div>
                                        <div class="h-2 flex-1 rounded bg-blue-300 dark:bg-white/20"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Event Approval Notifications -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            {{ __('messages.email') }}
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.why_create_account_notifications_title') }}</h2>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">{{ __('messages.why_create_account_notifications_desc') }}</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-2 flex items-center gap-2">
                                <div class="h-2 w-2 rounded-full bg-green-500"></div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Event Schedule</span>
                            </div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">Your event was approved!</div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Jazz Night is now live...</div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Edit Events Anytime -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            {{ __('messages.edit') }}
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.why_create_account_edit_title') }}</h2>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">{{ __('messages.why_create_account_edit_desc') }}</p>
                        <div class="mt-auto space-y-2" aria-hidden="true">
                            @foreach (['Fix typos', 'Change times', 'Add images'] as $editItem)
                                <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-2 dark:bg-white/5">
                                    <svg aria-hidden="true" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ $editItem }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Track Submissions (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    {{ __('messages.dashboard') }}
                                </div>
                                <h2 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white">{{ __('messages.why_create_account_track_title') }}</h2>
                                <p class="text-lg text-gray-500 dark:text-gray-400">{{ __('messages.why_create_account_track_desc') }}</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between rounded-xl border border-green-200 bg-green-50 p-3 dark:border-green-500/20 dark:bg-green-500/10">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">Jazz Night</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Blue Note</div>
                                        </div>
                                        <span class="inline-flex items-center rounded bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-500/20 dark:text-green-300">Approved</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-xl border border-yellow-200 bg-yellow-50 p-3 dark:border-yellow-500/20 dark:bg-yellow-500/10">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">Summer Festival</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">City Park</div>
                                        </div>
                                        <span class="inline-flex items-center rounded bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300">{{ __('messages.pending') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Build Your Reputation -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                            {{ __('messages.reputation') }}
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.why_create_account_reputation_title') }}</h2>
                        <p class="text-gray-500 dark:text-gray-400">{{ __('messages.why_create_account_reputation_desc') }}</p>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Connect with Curators -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700 dark:border-blue-800/30 dark:bg-blue-900/40 dark:text-blue-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            {{ __('messages.curators') }}
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.why_create_account_connect_title') }}</h2>
                        <p class="text-gray-500 dark:text-gray-400">{{ __('messages.why_create_account_connect_desc') }}</p>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Get Started Fast -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-teal-200 bg-teal-100 px-3 py-1.5 text-sm font-medium text-teal-700 dark:border-teal-800/30 dark:bg-teal-900/40 dark:text-teal-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            {{ __('messages.fast') }}
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.why_create_account_fast_title') }}</h2>
                        <p class="text-gray-500 dark:text-gray-400">{{ __('messages.why_create_account_fast_desc') }}</p>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Access Free Features (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-emerald-200 bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:border-emerald-800/30 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                                    </svg>
                                    {{ __('messages.free') }}
                                </div>
                                <h2 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white">{{ __('messages.why_create_account_features_title') }}</h2>
                                <p class="text-lg text-gray-500 dark:text-gray-400">{{ __('messages.why_create_account_features_desc') }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-4" aria-hidden="true">
                                @php
                                    $freeFeatures = [
                                        ['M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', __('messages.calendar')],
                                        ['M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', __('messages.analytics')],
                                        ['M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', __('messages.newsletters')],
                                        ['M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z', __('messages.tickets')],
                                    ];
                                @endphp
                                @foreach ($freeFeatures as $ff)
                                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-center dark:border-white/10 dark:bg-[#0f0f14]">
                                        <svg aria-hidden="true" class="mx-auto mb-2 h-8 w-8 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ff[0] }}" />
                                        </svg>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $ff[1] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Own Your Data -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-sky-200 bg-sky-100 px-3 py-1.5 text-sm font-medium text-sky-700 dark:border-sky-800/30 dark:bg-sky-900/40 dark:text-sky-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            {{ __('messages.data') }}
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.why_create_account_data_title') }}</h2>
                        <p class="text-gray-500 dark:text-gray-400">{{ __('messages.why_create_account_data_desc') }}</p>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. How it Works                                             -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-24 dark:bg-[#0f0f14]">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-16 text-center">
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>{{ __('messages.why_create_account_how_title') }}</h2>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                @php
                    $howSteps = [
                        [__('messages.why_create_account_step1_title'), __('messages.why_create_account_step1_desc')],
                        [__('messages.why_create_account_step2_title'), __('messages.why_create_account_step2_desc')],
                        [__('messages.why_create_account_step3_title'), __('messages.why_create_account_step3_desc')],
                    ];
                @endphp
                @foreach ($howSteps as $si => $step)
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
    <!-- 5. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-blue-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(37, 99, 235, 0.3), rgba(37, 99, 235, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-keys absolute bottom-6 left-0 right-0 mx-auto flex h-14 items-center justify-center gap-6 px-8 opacity-40" style="mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);">
                        @for ($i = 0; $i < 9; $i++)
                            @php $sz = [18, 26, 16, 22, 30][$i % 5]; $dur = 2.6 + ($i % 5) * 0.4; $delay = ($i % 7) * 0.3; @endphp
                            <span class="es-key" style="--k-dur: {{ $dur }}s; --k-delay: {{ $delay }}s; color: rgba(96, 165, 250, 0.8);">
                                <svg width="{{ $sz }}" height="{{ $sz }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4-2a6 6 0 01-7.743 5.743L11 14H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" /></svg>
                            </span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        {{ __('messages.why_create_account_ready_title') }}
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        {{ __('messages.why_create_account_ready_subtitle') }}
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
                                {{ __('messages.why_create_account_cta') }}
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1" @if(in_array(request()->get('lang'), ['he', 'ar'])) style="transform: rotate(180deg)" @endif fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-gray-400">{{ __('messages.why_create_account_no_cc') }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Local confetti (no CDN) + motion engines -->
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}"></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
