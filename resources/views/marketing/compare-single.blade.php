<x-marketing-layout>
    <x-slot name="title">{{ $name }} Alternative | Event Schedule vs {{ $name }}</x-slot>
    <x-slot name="description">{{ $description }}</x-slot>
    <x-slot name="keywords">{{ $keywords }}</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">{{ $name }} Alternative</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": ["WebPage", "ItemPage"],
        "name": "Event Schedule vs {{ $name }}",
        "description": "{{ $description }}",
        "url": "{{ config('app.url') }}/{{ $slug }}",
        "mainEntity": {
            "@type": "SoftwareApplication",
            "name": "Event Schedule",
            "applicationCategory": "BusinessApplication",
            "operatingSystem": ["Web", "Android", "iOS"]
        }
    }
    </script>
    </x-slot>

    <style>
        .compare-table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .compare-table-wrapper::-webkit-scrollbar {
            height: 6px;
        }
        .compare-table-wrapper::-webkit-scrollbar-track {
            background: transparent;
        }
        .compare-table-wrapper::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.5);
            border-radius: 3px;
        }
        @media (max-width: 768px) {
            .compare-table th:first-child,
            .compare-table td:first-child {
                position: sticky;
                left: 0;
                z-index: 10;
            }
            .compare-table th:first-child {
                z-index: 20;
            }
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <!-- Breadcrumb -->
            <div class="mb-6">
                <a href="{{ route('marketing.compare') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                    &larr; Compare all platforms
                </a>
            </div>

            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Platform comparison</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Event Schedule<br>
                <span class="text-gradient">vs {{ $name }}</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto">
                {{ $tagline }}
            </p>
        </div>
    </section>

    <!-- Transition -->
    <div class="section-fade-to-gray h-24"></div>

    <!-- Comparison Table -->
    <section class="bg-gray-100 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="compare-table-wrapper rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5">
                <table class="compare-table w-full text-left">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="bg-white dark:bg-[#0f0f14] px-6 py-5 text-sm font-semibold text-gray-900 dark:text-white min-w-[180px]">Feature</th>
                            <th class="px-6 py-5 text-sm font-semibold text-blue-600 dark:text-blue-400 min-w-[180px] bg-blue-50/50 dark:bg-blue-500/5">
                                Event Schedule
                            </th>
                            <th class="px-6 py-5 text-sm font-semibold text-gray-900 dark:text-white min-w-[180px]">{{ $name }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($sections as $sectionName => $rows)
                            <tr>
                                <td colspan="3" class="bg-gray-50 dark:bg-white/[0.03] px-6 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    {{ $sectionName }}
                                </td>
                            </tr>
                            @foreach ($rows as $row)
                                @php
                                    $esWins = $row[3] ?? false;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="bg-white dark:bg-[#0f0f14] px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">{{ $row[0] }}</td>
                                    <td class="px-6 py-4 text-sm {{ $esWins ? 'bg-emerald-50/50 dark:bg-emerald-500/5' : 'bg-blue-50/50 dark:bg-blue-500/5' }}">
                                        @if (str_starts_with($row[1], 'Yes'))
                                            <span class="inline-flex items-center gap-1.5 font-medium">
                                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                                @if (strlen($row[1]) > 3)
                                                    <span class="text-emerald-600/70 dark:text-emerald-400/70 text-xs">{{ substr($row[1], 4) }}</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-gray-700 dark:text-gray-300">{{ $row[1] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        @if (str_starts_with($row[2], 'Yes'))
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                                @if (strlen($row[2]) > 3)
                                                    <span class="text-emerald-600/70 dark:text-emerald-400/70 text-xs">{{ substr($row[2], 4) }}</span>
                                                @endif
                                            </span>
                                        @elseif (str_starts_with($row[2], 'No'))
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                @if (strlen($row[2]) > 2)
                                                    <span class="text-gray-400 dark:text-gray-500 text-xs">{{ trim(substr($row[2], 2)) }}</span>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-gray-700 dark:text-gray-300">{{ $row[2] }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Where Event Schedule Shines -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Where Event Schedule Shines
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Key advantages over {{ $name }} that make Event Schedule the better choice.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-5xl mx-auto">
                @foreach ($key_advantages as $advantage)
                    <div class="bg-gradient-to-br {{ $advantage['gradient'] }} rounded-2xl p-8 border {{ $advantage['border'] }}">
                        <div class="w-12 h-12 rounded-xl {{ $advantage['icon_bg'] }} flex items-center justify-center mb-5">
                            @if ($advantage['icon'] === 'dollar')
                                <svg class="w-6 h-6 {{ $advantage['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            @elseif ($advantage['icon'] === 'globe')
                                <svg class="w-6 h-6 {{ $advantage['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                            @elseif ($advantage['icon'] === 'ai')
                                <svg class="w-6 h-6 {{ $advantage['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            @elseif ($advantage['icon'] === 'code')
                                <svg class="w-6 h-6 {{ $advantage['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                            @elseif ($advantage['icon'] === 'calendar')
                                <svg class="w-6 h-6 {{ $advantage['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            @elseif ($advantage['icon'] === 'image')
                                <svg class="w-6 h-6 {{ $advantage['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            @elseif ($advantage['icon'] === 'percent')
                                <svg class="w-6 h-6 {{ $advantage['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                                </svg>
                            @elseif ($advantage['icon'] === 'mail')
                                <svg class="w-6 h-6 {{ $advantage['icon_color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            @endif
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ $advantage['title'] }}</h3>
                        <p class="text-gray-600 dark:text-gray-400">{{ $advantage['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Transition -->
    <div class="section-fade-to-gray h-24"></div>

    <!-- About Competitor -->
    <section class="bg-gray-100 dark:bg-[#0f0f14] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- About the competitor -->
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">
                        About {{ $name }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        {{ $about }}
                    </p>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ $name }}'s strengths</h3>
                    <ul class="space-y-3">
                        @foreach ($competitor_strengths as $strength)
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-gray-600 dark:text-gray-400">{{ $strength }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Why switch to ES -->
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">
                        Why choose Event Schedule?
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Event Schedule offers a unique combination of features that no other platform matches: zero platform fees, open source transparency, and powerful AI tools.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-600 dark:text-gray-400">Zero platform fees on all ticket sales, at any plan level</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-600 dark:text-gray-400">Fully open source with selfhosting option for complete control</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-600 dark:text-gray-400">AI-powered event parsing and automatic graphics generation</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-600 dark:text-gray-400">Two-way Google Calendar and CalDAV sync included free</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Transition -->
    <div class="section-fade-from-gray h-24"></div>

    <!-- Cross-links -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    Also compare with
                </h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-2xl mx-auto">
                @foreach ($cross_links as $link)
                    <a href="{{ route($link['route']) }}" class="group flex items-center justify-between p-6 rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 hover:border-blue-300 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/5 transition-all">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Event Schedule vs</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ $link['name'] }}</div>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-blue-600 to-sky-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Ready to switch?
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Create your free schedule today. No credit card required, no platform fees ever.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
