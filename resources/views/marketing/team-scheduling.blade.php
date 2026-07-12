<x-marketing-layout>
    <x-slot name="title">Team Event Scheduling & Permissions | Event Schedule</x-slot>
    <x-slot name="description">Invite team members, manage permissions, and coordinate schedules together. Collaborate on events with your team using flexible role-based access.</x-slot>
    <x-slot name="breadcrumbTitle">Team Scheduling</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Team Scheduling",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Invite team members, manage permissions, and coordinate schedules together. Collaborate on events with your team using flexible role-based access.",
        "featureList": [
            "Team member invitations",
            "Role-based permissions",
            "Collaborative event management",
            "Shared calendar access",
            "Member availability tracking"
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
                "name": "What permissions do team members have?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Team members can create and edit events on your schedule. The schedule owner retains full administrative control including billing, settings, and member management."
                }
            },
            {
                "@type": "Question",
                "name": "How do I invite team members?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Go to your schedule settings and enter the email address of the person you want to invite. They will receive an invitation to join your schedule as a team member."
                }
            },
            {
                "@type": "Question",
                "name": "Is there a limit on team members?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "The free plan includes team collaboration. The Enterprise plan supports multiple team members per account, so your whole team can manage the schedule together."
                }
            }
        ]
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
        /* For team-scheduling "The Lineup" styles. The shared es-* motion system lives
           in marketing.css; this holds the cyan/teal glow gradient, the drifting roster
           card, and the avatar-lineup motif (members popping in like a team assembling). */
        .text-gradient-team {
            background: linear-gradient(135deg, #0891b2, #06b6d4, #14b8a6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(8, 145, 178, 0.3);
        }
        .dark .text-gradient-team {
            background: linear-gradient(135deg, #22d3ee, #67e8f9, #2dd4bf);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(34, 211, 238, 0.3);
        }
        @keyframes es-team-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-team-float { animation: es-team-float 6s ease-in-out infinite; }

        /* Avatar-lineup motif: circles pop in one after another in a wave, like a
           team roster filling up as members accept their invites. */
        .es-avatars { display: flex; align-items: center; }
        .es-avatar {
            flex: 0 0 auto; border-radius: 9999px;
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.75), rgba(20, 184, 166, 0.75));
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.06);
            animation: es-avatar-pop var(--av-dur, 2.8s) ease-in-out infinite;
            animation-delay: var(--av-delay, 0s);
        }
        @keyframes es-avatar-pop {
            0%, 100% { opacity: 0.3; transform: scale(0.62); }
            50% { opacity: 1; transform: scale(1); box-shadow: 0 0 12px rgba(6, 182, 212, 0.55); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-team-float, .es-avatar, .animate-pulse-slow { animation: none !important; }
            .es-avatar { opacity: 0.62; transform: scale(0.85); }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: team scheduling                                     -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(8, 145, 178, 0.3), rgba(8, 145, 178, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(20, 184, 166, 0.28), rgba(20, 184, 166, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(6, 182, 212, 0.14), rgba(6, 182, 212, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Avatar lineup along the bottom edge -->
            <div class="es-avatars absolute bottom-0 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-3 px-8 opacity-50 md:flex" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                @for ($i = 0; $i < 22; $i++)
                    @php $sz = [16, 22, 14, 20, 18][$i % 5]; $dur = 2.4 + ($i % 5) * 0.3; $delay = ($i % 11) * 0.2; @endphp
                    <span class="es-avatar" style="width: {{ $sz }}px; height: {{ $sz }}px; --av-dur: {{ $dur }}s; --av-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-cyan-600 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Collaborate together</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Team</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-team">scheduling</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Invite your team, assign permissions, and manage events together.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-cyan-600 to-teal-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-cyan-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-cyan-500/40">
                    Start for free
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.creating_schedules') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Scheduling guide
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
                <h2 class="es-balance text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Everything your team needs to <span class="text-gradient-team">work together</span></h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="90">

                <!-- Invite team members (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col gap-8 lg:flex-row lg:items-center">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                    Invite Members
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Invite team members via email</h3>
                                <p class="mb-6 text-gray-500 dark:text-gray-400 lg:text-lg">Add teammates by email address. They'll receive an invitation and can join instantly, even if they don't have an account yet.</p>
                                <div class="flex flex-wrap gap-3">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Email invitations</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Auto-create accounts</span>
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">Resend invites</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0 lg:w-72" aria-hidden="true">
                                <div class="space-y-3 rounded-2xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <!-- Team member 1 -->
                                    <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 dark:border-white/10 dark:bg-white/10">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-cyan-500 to-teal-500 text-sm font-semibold text-white">JD</div>
                                        <div class="min-w-0 flex-1">
                                            <div class="truncate text-sm font-medium text-gray-900 dark:text-white">John Doe</div>
                                            <div class="truncate text-xs text-gray-500 dark:text-gray-400">john@example.com</div>
                                        </div>
                                        <span class="inline-flex items-center rounded bg-cyan-100 px-2 py-0.5 text-xs text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300">Owner</span>
                                    </div>
                                    <!-- Team member 2 -->
                                    <div class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-3 dark:border-white/10 dark:bg-white/10">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-emerald-500 text-sm font-semibold text-white">AS</div>
                                        <div class="min-w-0 flex-1">
                                            <div class="truncate text-sm font-medium text-gray-900 dark:text-white">Alice Smith</div>
                                            <div class="truncate text-xs text-gray-500 dark:text-gray-400">alice@example.com</div>
                                        </div>
                                        <span class="inline-flex items-center rounded bg-teal-100 px-2 py-0.5 text-xs text-teal-700 dark:bg-teal-500/20 dark:text-teal-300">Admin</span>
                                    </div>
                                    <!-- Pending invite -->
                                    <div class="flex items-center gap-3 rounded-xl border border-dashed border-gray-300 bg-gray-100 p-3 dark:border-white/20 dark:bg-white/5">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-200 dark:bg-white/10">
                                            <svg aria-hidden="true" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="truncate text-sm text-gray-500 dark:text-gray-400">bob@example.com</div>
                                            <div class="text-xs text-gray-400 dark:text-gray-500">Invitation pending</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Role-based access -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            Permissions
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Role-based access</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Assign the right level of access to each team member based on their responsibilities.</p>
                        <div class="mt-auto space-y-2.5" aria-hidden="true">
                            <div class="es-ai-field flex items-center gap-3 rounded-xl border border-cyan-500/20 bg-cyan-500/10 p-3" style="--i: 0;">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-500/20">
                                    <svg class="h-4 w-4 text-cyan-500 dark:text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">Owner</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Full control &amp; billing</p>
                                </div>
                            </div>
                            <div class="es-ai-field flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 1;">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-500/20">
                                    <svg class="h-4 w-4 text-teal-500 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">Admin</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Manage events &amp; members</p>
                                </div>
                            </div>
                            <div class="es-ai-field flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 2;">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/20">
                                    <svg class="h-4 w-4 text-emerald-500 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">Follower</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">View-only access</p>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Shared editing -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Collaboration
                        </div>
                        <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Shared editing</h3>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">All team members with the right permissions can create and edit events on your schedule.</p>
                        <div class="mt-auto rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">Team Workshop</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Edited 2m ago</span>
                            </div>
                            <div class="flex -space-x-2">
                                <div class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-cyan-500 to-teal-500 text-xs font-semibold text-white dark:border-[#0f0f14]">J</div>
                                <div class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-white bg-gradient-to-br from-teal-500 to-emerald-500 text-xs font-semibold text-white dark:border-[#0f0f14]">A</div>
                                <div class="flex h-7 w-7 items-center justify-center rounded-full border-2 border-white bg-gray-300 text-xs text-gray-600 dark:border-[#0f0f14] dark:bg-white/10 dark:text-gray-300">+2</div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Track member availability (spans 2 cols) -->
                <div class="es-bento group relative lg:col-span-2" data-tilt="4" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-cyan-200 bg-cyan-100 px-3 py-1.5 text-sm font-medium text-cyan-700 dark:border-cyan-800/30 dark:bg-cyan-900/40 dark:text-cyan-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Availability
                                </div>
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white lg:text-3xl">Track member availability</h3>
                                <p class="text-gray-500 dark:text-gray-400 lg:text-lg">Team members can mark dates when they're unavailable, helping you coordinate schedules and plan events when everyone can participate.</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="mb-4 text-sm text-gray-500 dark:text-gray-400">January 2025</div>
                                <div class="grid grid-cols-7 gap-1 text-center text-xs">
                                    <div class="py-1 text-gray-500">S</div>
                                    <div class="py-1 text-gray-500">M</div>
                                    <div class="py-1 text-gray-500">T</div>
                                    <div class="py-1 text-gray-500">W</div>
                                    <div class="py-1 text-gray-500">T</div>
                                    <div class="py-1 text-gray-500">F</div>
                                    <div class="py-1 text-gray-500">S</div>
                                    <div class="py-2 text-gray-400 dark:text-gray-400">29</div>
                                    <div class="py-2 text-gray-400 dark:text-gray-400">30</div>
                                    <div class="py-2 text-gray-400 dark:text-gray-400">31</div>
                                    <div class="py-2 text-gray-900 dark:text-white">1</div>
                                    <div class="py-2 text-gray-900 dark:text-white">2</div>
                                    <div class="py-2 text-gray-900 dark:text-white">3</div>
                                    <div class="py-2 text-gray-900 dark:text-white">4</div>
                                    <div class="py-2 text-gray-900 dark:text-white">5</div>
                                    <div class="py-2 text-gray-900 dark:text-white">6</div>
                                    <div class="py-2 text-gray-900 dark:text-white">7</div>
                                    <div class="rounded-lg bg-red-500/20 py-2 text-red-500 dark:text-red-400">8</div>
                                    <div class="rounded-lg bg-red-500/20 py-2 text-red-500 dark:text-red-400">9</div>
                                    <div class="py-2 text-gray-900 dark:text-white">10</div>
                                    <div class="py-2 text-gray-900 dark:text-white">11</div>
                                    <div class="py-2 text-gray-900 dark:text-white">12</div>
                                    <div class="py-2 text-gray-900 dark:text-white">13</div>
                                    <div class="py-2 text-gray-900 dark:text-white">14</div>
                                    <div class="rounded-lg bg-cyan-500/20 py-2 text-cyan-600 dark:text-cyan-400">15</div>
                                    <div class="rounded-lg bg-cyan-500/20 py-2 text-cyan-600 dark:text-cyan-400">16</div>
                                    <div class="py-2 text-gray-900 dark:text-white">17</div>
                                    <div class="py-2 text-gray-900 dark:text-white">18</div>
                                </div>
                                <div class="mt-4 flex items-center gap-4 border-t border-gray-200 pt-4 text-xs dark:border-white/10">
                                    <div class="flex items-center gap-2">
                                        <div class="h-3 w-3 rounded border border-red-500/40 bg-red-500/20"></div>
                                        <span class="text-gray-500 dark:text-gray-400">Unavailable</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="h-3 w-3 rounded border border-cyan-500/40 bg-cyan-500/20"></div>
                                        <span class="text-gray-500 dark:text-gray-400">Event scheduled</span>
                                    </div>
                                </div>
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
    <!-- 3. How it works (dark band)                                 -->
    <!-- ============================================================ -->
    <section class="bg-white px-2 py-14 dark:bg-[#0a0a0f] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(8, 145, 178, 0.24), rgba(8, 145, 178, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(20, 184, 166, 0.2), rgba(20, 184, 166, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-avatars absolute bottom-0 left-0 right-0 mx-auto flex h-14 max-w-4xl items-center justify-center gap-3 px-8 opacity-30" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                    @for ($i = 0; $i < 20; $i++)
                        @php $sz = [16, 22, 14, 20, 18][$i % 5]; $dur = 2.4 + ($i % 5) * 0.3; $delay = ($i % 11) * 0.2; @endphp
                        <span class="es-avatar" style="width: {{ $sz }}px; height: {{ $sz }}px; --av-dur: {{ $dur }}s; --av-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="mx-auto mb-14 max-w-3xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>How it <span class="text-gradient-team">works</span></h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Get your team collaborating in three simple steps.</p>
                </div>

                <div class="grid grid-cols-1 gap-8 md:grid-cols-3" data-reveal-group="90">
                    <!-- Step 1 -->
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-teal-500 text-2xl font-bold text-white shadow-lg shadow-cyan-500/25">
                            1
                        </div>
                        <h3 class="mb-3 text-xl font-semibold text-white">Invite your team</h3>
                        <p class="text-gray-300">
                            Go to your schedule settings and add team members by their email address. They'll receive an invitation immediately.
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-teal-500 text-2xl font-bold text-white shadow-lg shadow-cyan-500/25">
                            2
                        </div>
                        <h3 class="mb-3 text-xl font-semibold text-white">Set permissions</h3>
                        <p class="text-gray-300">
                            Choose whether each member is an Admin who can manage events, or a Follower with view-only access.
                        </p>
                    </div>

                    <!-- Step 3 -->
                    <div data-reveal class="text-center">
                        <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-teal-500 text-2xl font-bold text-white shadow-lg shadow-cyan-500/25">
                            3
                        </div>
                        <h3 class="mb-3 text-xl font-semibold text-white">Collaborate</h3>
                        <p class="text-gray-300">
                            Your team can now create events, update schedules, and coordinate together.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Perfect for teams                                        -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Perfect for teams of <span class="text-gradient-team">all kinds</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Team scheduling adapts to how your organization works.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="80">
                @php
                    $teamUseCases = [
                        ['Event Venues', 'Let multiple staff members manage bookings and coordinate room schedules across your venue.', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                        ['Bands &amp; Artists', 'All band members can add gigs and mark unavailable dates, making tour planning easier.', 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3'],
                        ['Community Groups', 'Empower volunteers and organizers to contribute events while maintaining oversight.', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                        ['Sports Teams', 'Coaches and team managers can coordinate practice schedules and game times together.', 'M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9'],
                        ['Event Agencies', 'Multiple planners can manage client events and share workload efficiently.', 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                        ['Religious Organizations', 'Staff and volunteers can coordinate services, meetings, and community events.', 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z'],
                    ];
                @endphp
                @foreach ($teamUseCases as $uc)
                    <div data-reveal class="flex flex-col rounded-2xl border border-gray-200 bg-gray-50 p-6 transition-all duration-300 hover:-translate-y-1 hover:border-cyan-300 hover:shadow-lg dark:border-white/10 dark:bg-white/[0.04] dark:hover:border-cyan-500/30">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-teal-500 text-white shadow-lg shadow-cyan-500/25">
                            <svg aria-hidden="true" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $uc[2] }}" />
                            </svg>
                        </div>
                        <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">{!! $uc[0] !!}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $uc[1] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Next feature                                             -->
    <!-- ============================================================ -->
    <section class="relative overflow-hidden bg-gray-50 py-20 dark:bg-[#0f0f14]">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="absolute left-1/4 top-10 h-[300px] w-[300px] rounded-full bg-sky-600/20 blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 h-[200px] w-[200px] rounded-full bg-blue-600/20 blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2" data-reveal-group="90">

                <a href="{{ marketing_url('/features/online-events') }}" data-reveal class="group block">
                    <div class="flex h-full flex-col rounded-3xl border border-sky-200 bg-gradient-to-br from-sky-100 to-blue-100 p-8 transition-all duration-300 hover:scale-[1.02] dark:border-white/10 dark:from-sky-900 dark:to-blue-900 lg:p-10">
                        <div class="flex flex-1 flex-col items-center gap-8 lg:flex-row">
                            <div class="flex flex-1 flex-col text-center lg:text-left">
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-sky-600 dark:text-white dark:group-hover:text-sky-300 lg:text-3xl">Online Events</h3>
                                <p class="mb-4 text-lg text-gray-600 dark:text-white/80">Host virtual events with any streaming platform. Easy toggle between in-person and online, with the link on every ticket.</p>
                                <span class="mt-auto inline-flex items-center gap-2 font-medium text-sky-600 transition-all group-hover:gap-3 dark:text-sky-400">
                                    Learn more
                                    <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </span>
                            </div>

                            <!-- Mini mockup: Toggle switch with streaming URL field -->
                            <div class="flex-shrink-0">
                                <div class="w-48 rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                    <div class="mb-4 flex items-center justify-between">
                                        <span class="text-xs text-gray-600 dark:text-gray-300">Online Event</span>
                                        <div class="relative h-5 w-10 rounded-full bg-sky-500">
                                            <div class="absolute right-0.5 top-0.5 h-4 w-4 rounded-full bg-white shadow"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="mb-1 text-[10px] text-gray-500">Streaming URL</div>
                                        <div class="flex items-center gap-1.5 rounded-lg border border-sky-400/30 bg-sky-500/20 px-2 py-1.5 text-xs text-sky-600 dark:text-sky-300">
                                            <svg aria-hidden="true" class="h-3 w-3 flex-shrink-0 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101" />
                                            </svg>
                                            <span class="truncate">zoom.us/j/123...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Popular with -->
                <div data-reveal class="flex h-full flex-col rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/5 lg:p-10">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-sky-500/20 bg-sky-500/10">
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Popular with</h3>
                    <div class="space-y-3">
                        <a href="{{ marketing_url('/for-venues') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-sky-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-sky-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Venues</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-theaters') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-sky-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-sky-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Theaters</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-curators') }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-sky-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-sky-500/30 dark:hover:bg-white/10">
                            <span class="font-medium text-gray-900 dark:text-white">Curators</span>
                            <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-team">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about team scheduling.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['What permissions do team members have?', 'Team members can create and edit events on your schedule. The schedule owner retains full administrative control including billing, settings, and member management.'],
                    ['How do I invite team members?', 'Go to your schedule settings and enter the email address of the person you want to invite. They will receive an invitation to join your schedule as a team member.'],
                    ['Is there a limit on team members?', 'The free plan includes team collaboration. The Enterprise plan supports multiple team members per account, so your whole team can manage the schedule together.'],
                ] as [$q, $a])
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
    <!-- 7. Related features                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-bold text-gray-900 dark:text-white md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="80">
                <div data-reveal>
                    <x-feature-link-card
                        name="Sub-schedules"
                        description="Organize events into categories within your schedule"
                        :url="marketing_url('/features/sub-schedules')"
                        icon-color="teal"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Custom Fields"
                        description="Collect additional info from attendees with custom form fields"
                        :url="marketing_url('/features/custom-fields')"
                        icon-color="amber"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card
                        name="Newsletters"
                        description="Send branded newsletters to followers and ticket buyers"
                        :url="marketing_url('/features/newsletters')"
                        icon-color="sky"
                    >
                        <x-slot:icon>
                            <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-cyan-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(8, 145, 178, 0.3), rgba(8, 145, 178, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-avatars absolute bottom-0 left-0 right-0 mx-auto flex h-14 max-w-3xl items-center justify-center gap-3 px-8 opacity-30" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                        @for ($i = 0; $i < 18; $i++)
                            @php $sz = [16, 22, 14, 20, 18][$i % 5]; $dur = 2.4 + ($i % 5) * 0.3; $delay = ($i % 11) * 0.2; @endphp
                            <span class="es-avatar" style="width: {{ $sz }}px; height: {{ $sz }}px; --av-dur: {{ $dur }}s; --av-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Better <span class="text-gradient-team">together</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Start collaborating with your team today. Free to use, no credit card required.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-team" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-cyan-600 to-teal-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-cyan-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-cyan-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Get Started Free
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
