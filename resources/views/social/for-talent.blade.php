<x-social-image-layout>
    <div class="relative w-[1200px] h-[630px] bg-[#0a0a0f] overflow-hidden">
        <!-- Background orbs -->
        <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-sky-600/20 rounded-full blur-[120px]"></div>

        <!-- Grid pattern -->
        <div class="absolute inset-0 grid-pattern"></div>

        <!-- Logo -->
        <img src="{{ asset('images/light_logo.png') }}" alt="" class="absolute top-10 left-12 h-10">

        <!-- Content -->
        <div class="absolute inset-0 flex flex-col items-center justify-center px-16">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-white/10 mb-8">
                <svg aria-hidden="true" class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
                <span class="text-sm text-gray-300">For Musicians, DJs & Performers</span>
            </div>

            <!-- Headline -->
            <h1 class="text-6xl font-bold text-white text-center mb-6 leading-tight">
                <span class="block">Share your shows</span>
                <span class="block text-gradient">with fans</span>
            </h1>

            <!-- Subtitle -->
            <p class="text-2xl text-gray-400 text-center max-w-2xl">
                One link. Every gig. Let your audience know where you're playing next.
            </p>
        </div>
    </div>
</x-social-image-layout>
