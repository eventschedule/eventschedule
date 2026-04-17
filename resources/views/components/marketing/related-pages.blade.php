@php
    $path = trim(request()->path(), '/');
    $related = config('marketing_related.' . $path, []);
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
            <div class="mb-8">
                <p class="text-sm font-semibold uppercase tracking-wider text-[var(--brand-blue)] mb-2">Related</p>
                <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">Keep exploring</h2>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 {{ $gridCols }} gap-4">
                @foreach ($related as $item)
                    <a href="{{ url($item['path']) }}"
                       class="group flex flex-col p-5 rounded-2xl bg-white dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 hover:border-[var(--brand-blue)] hover:shadow-md transition-all duration-200">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 group-hover:text-[var(--brand-blue)] transition-colors">
                            {{ $item['title'] }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed mb-4 flex-1">
                            {{ $item['blurb'] }}
                        </p>
                        <span class="mt-auto text-sm font-medium text-[var(--brand-blue)] inline-flex items-center gap-1">
                            Read more
                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
