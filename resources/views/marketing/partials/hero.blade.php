@props(['title', 'subtitle', 'showCta' => true])

<section class="hero-gradient py-20 lg:py-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight mb-6">
            {!! $title !!}
        </h1>
        <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto mb-10">
            {{ $subtitle }}
        </p>
        @if($showCta)
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white text-lg font-medium rounded-xl transition-colors shadow-lg shadow-blue-600/25">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
            <a href="{{ marketing_url('/features') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-900 dark:text-white text-lg font-medium rounded-xl transition-colors border border-gray-200 dark:border-gray-700">
                Learn More
            </a>
        </div>
        @endif
    </div>
</section>
