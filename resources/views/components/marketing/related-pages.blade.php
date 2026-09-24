@php
    $path = trim(request()->path(), '/');
    $related = config('marketing_related.' . $path, []);
    // The docs page for this one: an audience page's entry in config/marketing_guides.php, or
    // the guide whose manifest `feature` names this page. Beside the heading rather than as a
    // fifth card, so the grid below stays a complete row of four.
    $guide = \App\Utils\DocsUtils::guideForPath($path);
    $gridCols = match (min(count($related), 4)) {
        1 => 'lg:grid-cols-1',
        2 => 'lg:grid-cols-2',
        3 => 'lg:grid-cols-3',
        default => 'lg:grid-cols-4',
    };
@endphp
@if (!empty($related))
    <section class="bg-gray-50 dark:bg-[#0f0f14] border-t border-gray-200 dark:border-white/10 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-[var(--wp-link)] mb-2">Related</p>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">Keep exploring</h2>
                </div>
                @if ($guide)
                    <a href="{{ $guide['url'] }}"
                       class="inline-flex items-center gap-2 self-start rounded-lg text-sm font-medium text-[var(--wp-link)] transition-all duration-200 hover:gap-2.5 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] sm:self-auto">
                        <svg aria-hidden="true" class="h-4 w-4 flex-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span>Read the guide: {{ $guide['title'] }}</span>
                        <svg aria-hidden="true" class="h-4 w-4 flex-none rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                @endif
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 {{ $gridCols }} gap-4">
                @foreach ($related as $item)
                    {{-- The last thing on 79 pages, so it earns the same hover the rest of
                         the site uses: a 4px lift and a shadow, not just a border colour
                         change. The hairline along the top edge picks up the brand gradient
                         on hover, which is what makes a flat card grid feel like part of
                         this site rather than a footer. --}}
                    <a href="{{ url($item['path']) }}"
                       class="group relative flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 transition-all duration-200 hover:-translate-y-1 hover:border-blue-300 hover:shadow-lg dark:border-white/10 dark:bg-white/[0.03] dark:hover:border-blue-500/40">
                        <span aria-hidden="true"
                              class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-[#4E81FA] to-transparent opacity-0 transition-opacity duration-200 group-hover:opacity-100"></span>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-[var(--wp-link)] transition-colors">
                            {{ $item['title'] }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed mb-4 flex-1">
                            {{ $item['blurb'] }}
                        </p>
                        <span class="mt-auto text-sm font-medium text-[var(--wp-link)] inline-flex items-center gap-1 transition-all group-hover:gap-2">
                            Read more
                            <svg aria-hidden="true" class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
