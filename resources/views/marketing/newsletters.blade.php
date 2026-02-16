<x-marketing-layout>
    <x-slot name="title">Newsletter Builder for Events | Drag-and-Drop Email Marketing - Event Schedule</x-slot>
    <x-slot name="description">Send branded newsletters to followers and ticket buyers. Drag-and-drop editor, five professional templates, audience segments, A/B testing, and real-time delivery analytics.</x-slot>
    <x-slot name="keywords">event newsletter, email marketing events, drag and drop email builder, newsletter templates, audience segments, A/B testing, email analytics, event marketing</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">Newsletters</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule Newsletters",
        "description": "Send branded newsletters to followers and ticket buyers. Drag-and-drop editor, professional templates, audience segments, A/B testing, and delivery analytics.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "Event Email Marketing"
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Custom sky gradient for this page */
        .text-gradient {
            background: linear-gradient(135deg, #0284c7 0%, #0891b2 50%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .dark .text-gradient {
            background: linear-gradient(135deg, #38bdf8 0%, #22d3ee 50%, #7dd3fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-sky-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg aria-hidden="true" class="w-4 h-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Newsletters</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Newsletter<br>
                <span class="text-gradient">builder</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Send branded newsletters to your followers and ticket buyers with a drag-and-drop editor, professional templates, audience segments, A/B testing, and delivery analytics.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-sky-600 to-cyan-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-sky-500/25">
                    Get started free
                    <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-12 text-center">Everything you need to send great emails</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Row 1: Drag-and-Drop Builder (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Visual Editor
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Build with blocks</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-6">Compose newsletters visually with 10 block types: headings, rich text with markdown, images, buttons, event listings, social links, dividers, spacers, profile images, and header banners. Drag to reorder, clone, or delete blocks.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">10 block types</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Drag-and-drop</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Markdown support</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <!-- Builder mockup -->
                                <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-4 w-56">
                                    <div class="space-y-2">
                                        <!-- Heading block -->
                                        <div class="bg-white dark:bg-white/10 rounded-lg p-2.5 border border-gray-200 dark:border-white/10">
                                            <div class="text-[10px] text-sky-500 dark:text-sky-400 mb-1">Heading</div>
                                            <div class="text-sm font-bold text-gray-900 dark:text-white">This Week in Events</div>
                                        </div>
                                        <!-- Text block -->
                                        <div class="bg-white dark:bg-white/10 rounded-lg p-2.5 border border-gray-200 dark:border-white/10">
                                            <div class="text-[10px] text-sky-500 dark:text-sky-400 mb-1">Text</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Check out our upcoming shows...</div>
                                        </div>
                                        <!-- Events block -->
                                        <div class="bg-sky-50 dark:bg-sky-500/10 rounded-lg p-2.5 border border-sky-200 dark:border-sky-400/30">
                                            <div class="text-[10px] text-sky-500 dark:text-sky-400 mb-1">Events</div>
                                            <div class="text-xs text-gray-900 dark:text-white">3 events selected</div>
                                        </div>
                                        <!-- Button block -->
                                        <div class="bg-white dark:bg-white/10 rounded-lg p-2.5 border border-gray-200 dark:border-white/10 text-center">
                                            <div class="inline-flex px-4 py-1 bg-sky-500 text-white text-xs rounded-full">View All Events</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 1: Professional Templates -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                        5 Templates
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Start polished</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Choose from Modern, Classic, Minimal, Bold, or Compact. Each template has its own typography, color palette, button style, and event layout.</p>

                    <div class="flex gap-2 justify-center">
                        <div class="w-10 h-14 rounded-lg bg-sky-500 border-2 border-sky-300 dark:border-sky-400" title="Modern"></div>
                        <div class="w-10 h-14 rounded-lg bg-amber-700 border-2 border-amber-500 dark:border-amber-400" title="Classic"></div>
                        <div class="w-10 h-14 rounded-lg bg-gray-400 border-2 border-gray-300 dark:border-gray-500" title="Minimal"></div>
                        <div class="w-10 h-14 rounded-lg bg-rose-700 border-2 border-rose-500 dark:border-rose-400" title="Bold"></div>
                        <div class="w-10 h-14 rounded-lg bg-emerald-600 border-2 border-emerald-400 dark:border-emerald-400" title="Compact"></div>
                    </div>
                </div>

                <!-- Row 2: Audience Segments -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Smart Targeting
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Reach the right people</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Target all followers, ticket buyers (filter by event or date range), manual email lists, or event group members. Combine segments and auto-deduplicate.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-sky-100 dark:bg-sky-500/20 border border-sky-200 dark:border-sky-400/30">
                            <div class="w-3 h-3 rounded-full bg-sky-500"></div>
                            <span class="text-gray-900 dark:text-white text-sm">All Followers</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                            <div class="w-3 h-3 rounded-full bg-cyan-500"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Ticket Buyers</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                            <div class="w-3 h-3 rounded-full bg-teal-500"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Manual List</span>
                        </div>
                        <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                            <div class="w-3 h-3 rounded-full bg-sky-500"></div>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Group Members</span>
                        </div>
                    </div>
                </div>

                <!-- Row 2: A/B Testing (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                Optimize
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Test and learn</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg">Split-test subject lines or content across a sample of your audience (5-50%). Pick the winner by open rate or click rate. Set wait time from 1 to 72 hours. The winning variant is automatically sent to the remaining recipients.</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Variant A -->
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Variant A</div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">24%</div>
                                <div class="text-sky-400 text-sm">Open rate</div>
                                <div class="mt-2 h-1.5 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                    <div class="h-full bg-sky-400 rounded-full" style="width: 24%"></div>
                                </div>
                            </div>
                            <!-- Variant B (winner) -->
                            <div class="bg-sky-50 dark:bg-sky-500/10 rounded-xl p-4 border border-sky-200 dark:border-sky-400/30 text-center">
                                <div class="text-xs text-sky-600 dark:text-sky-300 mb-2">Variant B</div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">31%</div>
                                <div class="text-sky-400 text-sm">Open rate</div>
                                <div class="mt-2 h-1.5 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                    <div class="h-full bg-sky-500 rounded-full" style="width: 31%"></div>
                                </div>
                                <div class="mt-2 inline-flex items-center gap-1 text-[10px] text-emerald-600 dark:text-emerald-400 font-medium">
                                    <svg aria-hidden="true" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    Winner
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Delivery Analytics (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                </svg>
                                Real-time Stats
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Track every send</h3>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-6">Monitor opens, clicks, and failures in real time. View opens-over-time and clicks-over-time charts, top clicked links, and per-recipient engagement details. A/B test results shown side-by-side.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Open tracking</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Click tracking</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Top links</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-4 w-64">
                                    <!-- Stats cards -->
                                    <div class="grid grid-cols-3 gap-2 mb-3">
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-gray-900 dark:text-white">1,248</div>
                                            <div class="text-[10px] text-gray-500 dark:text-gray-400">Sent</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-sky-600 dark:text-sky-400">42%</div>
                                            <div class="text-[10px] text-gray-500 dark:text-gray-400">Opens</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-lg font-bold text-cyan-600 dark:text-cyan-400">18%</div>
                                            <div class="text-[10px] text-gray-500 dark:text-gray-400">Clicks</div>
                                        </div>
                                    </div>
                                    <!-- Mini line chart -->
                                    <div class="flex items-end justify-between h-16 gap-1">
                                        <div class="w-4 bg-sky-500/30 rounded-t" style="height: 20%"></div>
                                        <div class="w-4 bg-sky-500/40 rounded-t" style="height: 35%"></div>
                                        <div class="w-4 bg-sky-500/50 rounded-t" style="height: 65%"></div>
                                        <div class="w-4 bg-sky-500/60 rounded-t" style="height: 90%"></div>
                                        <div class="w-4 bg-sky-500/70 rounded-t" style="height: 100%"></div>
                                        <div class="w-4 bg-sky-500/75 rounded-t" style="height: 95%"></div>
                                        <div class="w-4 bg-sky-500/80 rounded-t" style="height: 85%"></div>
                                        <div class="w-4 bg-sky-500/80 rounded-t" style="height: 80%"></div>
                                    </div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 mt-1 text-center">Opens over time</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Design & Deliver -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                        Full Control
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Customize everything</h3>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Set background, accent, and text colors. Choose from 5 font families. Pick rounded or square buttons. Schedule sends for later. Send test emails first. Custom SMTP per schedule. One-click unsubscribe with compliance headers.</p>

                    <div class="flex items-center justify-center gap-4">
                        <!-- Color dots -->
                        <div class="flex gap-2">
                            <div class="w-8 h-8 rounded-full bg-sky-500 border-2 border-white dark:border-white/20 shadow"></div>
                            <div class="w-8 h-8 rounded-full bg-cyan-500 border-2 border-white dark:border-white/20 shadow"></div>
                            <div class="w-8 h-8 rounded-full bg-gray-800 dark:bg-gray-200 border-2 border-white dark:border-white/20 shadow"></div>
                        </div>
                        <!-- Calendar icon -->
                        <div class="bg-gray-100 dark:bg-white/10 rounded-lg p-2 border border-gray-200 dark:border-white/10">
                            <svg aria-hidden="true" class="w-6 h-6 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Guide & Next Feature -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-lime-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-green-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Read the guide -->
                <a href="{{ route('marketing.docs.newsletters') }}" class="group block">
                    <div class="h-full bg-white dark:bg-white/5 rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-sky-500/10 border border-sky-500/20 mb-6">
                            <svg aria-hidden="true" class="w-6 h-6 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">Read the guide</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">Learn how to get the most out of newsletters.</p>
                        <span class="inline-flex items-center text-sky-500 dark:text-sky-400 font-medium group-hover:gap-3 gap-2 transition-all">
                            Read guide
                            <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- Next feature -->
                <a href="{{ marketing_url('/features/calendar-sync') }}" class="group block">
                    <div class="h-full bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 rounded-3xl border border-sky-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-sky-600 dark:group-hover:text-sky-300 transition-colors">Calendar Sync</h3>
                        <p class="text-gray-600 dark:text-white/80 text-lg mb-4">Two-way sync with Google Calendar. Let attendees add events to Apple, Google, or Outlook calendars.</p>
                        <span class="inline-flex items-center text-sky-500 dark:text-sky-400 font-medium group-hover:gap-3 gap-2 transition-all">
                            Learn more
                            <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-sky-600 to-cyan-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Start sending newsletters today
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Reach your audience with branded emails. No credit card required.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-sky-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Start for free
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>

    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule Newsletters",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "Email Marketing Software",
        "operatingSystem": "Web",
        "description": "Send branded newsletters to followers and ticket buyers with a drag-and-drop editor, professional templates, audience segments, A/B testing, and real-time delivery analytics.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Included free"
        },
        "featureList": [
            "Drag-and-drop block editor",
            "5 professional templates",
            "Audience segments",
            "A/B testing",
            "Real-time delivery analytics",
            "Open and click tracking"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
