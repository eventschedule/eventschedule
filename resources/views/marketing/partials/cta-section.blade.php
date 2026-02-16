@props(['title' => 'Ready to get started?', 'subtitle' => 'Create your free schedule in seconds.'])

<section class="py-20 bg-gradient-to-r from-blue-600 to-sky-600">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
            {{ $title }}
        </h2>
        <p class="text-xl text-blue-100 mb-8">
            {{ $subtitle }}
        </p>
        <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white hover:bg-gray-100 text-blue-600 text-lg font-medium rounded-xl transition-colors shadow-lg">
            Get Started Free
            <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </a>
    </div>
</section>
