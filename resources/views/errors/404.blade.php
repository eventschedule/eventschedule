<x-marketing-layout>
    <x-slot name="title">Page Not Found - Event Schedule</x-slot>
    <x-slot name="description">The page you are looking for could not be found.</x-slot>

    <section class="relative bg-[#0a0a0f] min-h-[70vh] flex items-center justify-center overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-sky-500/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center py-24">
            <p class="text-9xl md:text-[12rem] font-bold text-gradient leading-none mb-4">404</p>

            <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">
                Page not found
            </h1>

            <p class="text-xl text-gray-400 mb-10 max-w-md mx-auto">
                The page you're looking for doesn't exist or has been moved.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ marketing_url('/') }}" class="inline-flex items-center px-6 py-3 text-base font-semibold text-white bg-gradient-to-r from-[#4E81FA] to-sky-500 rounded-xl hover:scale-105 transition-all shadow-lg shadow-blue-500/25">
                    Go to homepage
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                </a>
                <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 text-base font-semibold text-white glass border border-white/10 rounded-xl hover:bg-white/10 transition-all">
                    Sign in
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                </a>
            </div>
        </div>
    </section>
</x-marketing-layout>
