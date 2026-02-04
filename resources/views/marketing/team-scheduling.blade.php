<x-marketing-layout>
    <x-slot name="title">Team Event Scheduling | Permissions & Collaboration - Event Schedule</x-slot>
    <x-slot name="description">Invite team members, manage permissions, and coordinate schedules together. Collaborate on events with your team using flexible role-based access.</x-slot>
    <x-slot name="keywords">team scheduling, team collaboration, event management, shared calendar, team permissions, role-based access, member availability, team coordination</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">Team Scheduling</x-slot>

    <style>
        /* Custom cyan gradient for this page */
        .text-gradient {
            background: linear-gradient(135deg, #06b6d4 0%, #0891b2 50%, #0e7490 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        @media (prefers-reduced-motion: reduce) {
            .animate-pulse-slow,
            .animate-float {
                animation: none;
            }
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-teal-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Collaborate together</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Team<br>
                <span class="text-gradient">Scheduling</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Invite your team, assign permissions, and manage events together seamlessly.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-cyan-600 to-teal-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-cyan-500/25">
                    Start for free
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Invite Team Members (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-teal-100 dark:from-cyan-900 dark:to-teal-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                Invite Members
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Invite team members via email</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-6">Add teammates by email address. They'll receive an invitation and can join instantly, even if they don't have an account yet.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Email invitations</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Auto-create accounts</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Resend invites</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="animate-float">
                                <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl p-5 border border-gray-200 dark:border-white/10 space-y-3 max-w-xs">
                                    <!-- Team member 1 -->
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-200 dark:bg-white/10 border border-gray-200 dark:border-white/10">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-cyan-500 to-teal-500 flex items-center justify-center text-white font-semibold text-sm">JD</div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-gray-900 dark:text-white text-sm font-medium truncate">John Doe</div>
                                            <div class="text-gray-500 dark:text-gray-400 text-xs truncate">john@example.com</div>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-xs">Owner</span>
                                    </div>
                                    <!-- Team member 2 -->
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-200 dark:bg-white/10 border border-gray-200 dark:border-white/10">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center text-white font-semibold text-sm">AS</div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-gray-900 dark:text-white text-sm font-medium truncate">Alice Smith</div>
                                            <div class="text-gray-500 dark:text-gray-400 text-xs truncate">alice@example.com</div>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-xs">Admin</span>
                                    </div>
                                    <!-- Pending invite -->
                                    <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-100 dark:bg-white/5 border border-dashed border-gray-300 dark:border-white/20">
                                        <div class="w-10 h-10 rounded-full bg-gray-700 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="text-gray-500 dark:text-gray-400 text-sm truncate">bob@example.com</div>
                                            <div class="text-gray-500 text-xs">Invitation pending</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Permission Levels -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-emerald-100 dark:from-teal-900 dark:to-emerald-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Permissions
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Role-based access</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Assign the right level of access to each team member based on their responsibilities.</p>

                    <div class="space-y-3">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-cyan-500/10 border border-cyan-500/20">
                            <div class="w-8 h-8 rounded-lg bg-cyan-500/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                            </div>
                            <div>
                                <span class="text-gray-900 dark:text-white text-sm font-medium">Owner</span>
                                <p class="text-gray-500 dark:text-gray-400 text-xs">Full control & billing</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-200 dark:bg-white/10 border border-gray-200 dark:border-white/10">
                            <div class="w-8 h-8 rounded-lg bg-teal-500/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            </div>
                            <div>
                                <span class="text-gray-900 dark:text-white text-sm font-medium">Admin</span>
                                <p class="text-gray-500 dark:text-gray-400 text-xs">Manage events & members</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-200 dark:bg-white/10 border border-gray-200 dark:border-white/10">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            </div>
                            <div>
                                <span class="text-gray-900 dark:text-white text-sm font-medium">Follower</span>
                                <p class="text-gray-500 dark:text-gray-400 text-xs">View-only access</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shared Event Editing -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-blue-100 dark:from-cyan-900 dark:to-blue-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Collaboration
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Shared editing</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">All team members with the right permissions can create and edit events on your schedule.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-gray-900 dark:text-white text-sm font-medium">Team Workshop</span>
                            <span class="text-gray-500 dark:text-gray-400 text-xs">Edited 2m ago</span>
                        </div>
                        <div class="flex -space-x-2">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-cyan-500 to-teal-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs font-semibold">J</div>
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-teal-500 to-emerald-500 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs font-semibold">A</div>
                            <div class="w-7 h-7 rounded-full bg-gray-600 border-2 border-gray-200 dark:border-[#0a0a0f] flex items-center justify-center text-white text-xs">+2</div>
                        </div>
                    </div>
                </div>

                <!-- Member Availability (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Availability
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Track member availability</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg">Team members can mark dates when they're unavailable, helping you coordinate schedules and plan events when everyone can participate.</p>
                        </div>
                        <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl p-6 border border-gray-200 dark:border-white/10">
                            <div class="text-gray-500 dark:text-gray-400 text-sm mb-4">January 2025</div>
                            <div class="grid grid-cols-7 gap-1 text-center text-xs">
                                <div class="text-gray-500 py-1">S</div>
                                <div class="text-gray-500 py-1">M</div>
                                <div class="text-gray-500 py-1">T</div>
                                <div class="text-gray-500 py-1">W</div>
                                <div class="text-gray-500 py-1">T</div>
                                <div class="text-gray-500 py-1">F</div>
                                <div class="text-gray-500 py-1">S</div>
                                <!-- Calendar days -->
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
                                <div class="py-2 rounded-lg bg-red-500/20 text-red-400">8</div>
                                <div class="py-2 rounded-lg bg-red-500/20 text-red-400">9</div>
                                <div class="py-2 text-gray-900 dark:text-white">10</div>
                                <div class="py-2 text-gray-900 dark:text-white">11</div>
                                <div class="py-2 text-gray-900 dark:text-white">12</div>
                                <div class="py-2 text-gray-900 dark:text-white">13</div>
                                <div class="py-2 text-gray-900 dark:text-white">14</div>
                                <div class="py-2 rounded-lg bg-cyan-500/20 text-cyan-400">15</div>
                                <div class="py-2 rounded-lg bg-cyan-500/20 text-cyan-400">16</div>
                                <div class="py-2 text-gray-900 dark:text-white">17</div>
                                <div class="py-2 text-gray-900 dark:text-white">18</div>
                            </div>
                            <div class="flex items-center gap-4 mt-4 pt-4 border-t border-gray-200 dark:border-white/10 text-xs">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded bg-red-500/20 border border-red-500/40"></div>
                                    <span class="text-gray-500 dark:text-gray-400">Unavailable</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded bg-cyan-500/20 border border-cyan-500/40"></div>
                                    <span class="text-gray-500 dark:text-gray-400">Event scheduled</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    How it works
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Get your team collaborating in three simple steps.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Step 1 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-teal-500 text-white rounded-2xl flex items-center justify-center mb-6 mx-auto shadow-lg shadow-cyan-500/25 text-2xl font-bold">
                        1
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Invite your team</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Go to your schedule settings and add team members by their email address. They'll receive an invitation immediately.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-teal-500 text-white rounded-2xl flex items-center justify-center mb-6 mx-auto shadow-lg shadow-cyan-500/25 text-2xl font-bold">
                        2
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Set permissions</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Choose whether each member is an Admin who can manage events, or a Follower with view-only access.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-teal-500 text-white rounded-2xl flex items-center justify-center mb-6 mx-auto shadow-lg shadow-cyan-500/25 text-2xl font-bold">
                        3
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Collaborate</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Your team can now create events, update schedules, and coordinate together seamlessly.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Use Cases Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Perfect for teams of all kinds
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Team scheduling adapts to how your organization works.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Event Venues -->
                <div class="bg-gradient-to-br from-cyan-50 to-teal-50 dark:from-cyan-900/30 dark:to-teal-900/30 rounded-2xl p-6 border border-cyan-200 dark:border-cyan-500/20">
                    <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-teal-500 text-white rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-cyan-500/25">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Event Venues</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Let multiple staff members manage bookings and coordinate room schedules across your venue.
                    </p>
                </div>

                <!-- Music Bands -->
                <div class="bg-gradient-to-br from-cyan-50 to-teal-50 dark:from-cyan-900/30 dark:to-teal-900/30 rounded-2xl p-6 border border-cyan-200 dark:border-cyan-500/20">
                    <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-teal-500 text-white rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-cyan-500/25">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Bands & Artists</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        All band members can add gigs and mark unavailable dates, making tour planning easier.
                    </p>
                </div>

                <!-- Community Organizations -->
                <div class="bg-gradient-to-br from-cyan-50 to-teal-50 dark:from-cyan-900/30 dark:to-teal-900/30 rounded-2xl p-6 border border-cyan-200 dark:border-cyan-500/20">
                    <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-teal-500 text-white rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-cyan-500/25">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Community Groups</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Empower volunteers and organizers to contribute events while maintaining oversight.
                    </p>
                </div>

                <!-- Sports Teams -->
                <div class="bg-gradient-to-br from-cyan-50 to-teal-50 dark:from-cyan-900/30 dark:to-teal-900/30 rounded-2xl p-6 border border-cyan-200 dark:border-cyan-500/20">
                    <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-teal-500 text-white rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-cyan-500/25">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Sports Teams</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Coaches and team managers can coordinate practice schedules and game times together.
                    </p>
                </div>

                <!-- Event Agencies -->
                <div class="bg-gradient-to-br from-cyan-50 to-teal-50 dark:from-cyan-900/30 dark:to-teal-900/30 rounded-2xl p-6 border border-cyan-200 dark:border-cyan-500/20">
                    <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-teal-500 text-white rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-cyan-500/25">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Event Agencies</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Multiple planners can manage client events and share workload efficiently.
                    </p>
                </div>

                <!-- Religious Organizations -->
                <div class="bg-gradient-to-br from-cyan-50 to-teal-50 dark:from-cyan-900/30 dark:to-teal-900/30 rounded-2xl p-6 border border-cyan-200 dark:border-cyan-500/20">
                    <div class="w-12 h-12 bg-gradient-to-br from-cyan-500 to-teal-500 text-white rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-cyan-500/25">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Religious Organizations</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">
                        Staff and volunteers can coordinate services, meetings, and community events.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Next Feature -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <!-- Animated background blobs matching Online Events page's colors -->
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-sky-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-blue-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/features/online-events') }}" class="group block">
                <div class="bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 rounded-3xl border border-sky-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <!-- Text content -->
                        <div class="flex-1 text-center lg:text-left">
                            <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-sky-600 dark:group-hover:text-sky-300 transition-colors">Online Events</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-4">Host virtual events with any streaming platform. Easy toggle between in-person and online, with the link on every ticket.</p>
                            <span class="inline-flex items-center text-sky-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>

                        <!-- Mini mockup: Toggle switch with streaming URL field -->
                        <div class="flex-shrink-0">
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 p-4 w-48">
                                <!-- Toggle switch -->
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-gray-600 dark:text-gray-300 text-xs">Online Event</span>
                                    <div class="w-10 h-5 bg-sky-500 rounded-full relative">
                                        <div class="absolute right-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow"></div>
                                    </div>
                                </div>
                                <!-- URL field -->
                                <div>
                                    <div class="text-[10px] text-gray-500 mb-1">Streaming URL</div>
                                    <div class="bg-sky-500/20 rounded-lg px-2 py-1.5 text-sky-300 text-xs border border-sky-400/30 flex items-center gap-1.5">
                                        <svg class="w-3 h-3 text-sky-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-cyan-600 to-teal-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Better together
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Start collaborating with your team today. Free to use, no credit card required.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-cyan-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
